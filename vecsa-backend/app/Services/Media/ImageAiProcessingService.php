<?php

namespace App\Services\Media;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Edición de imágenes con IA usando la API Gemini (Nano Banana / modelos imagen).
 */
final class ImageAiProcessingService
{
    private const GEMINI_GENERATE_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    /** Modelo recomendado para edición imagen + texto (ajustable en Panel Dev). */
    public const DEFAULT_IMAGE_MODEL = 'gemini-3.1-flash-image-preview';

    /**
     * @return array{processed_base64: string, mime_type: string, public_id: null}
     */
    public function process(string $sourceUrl, string $action): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('No hay llave API de Gemini configurada. Configúrala en Panel desarrollo → Gemini (edición de fotos).');
        }

        $apiKey = $this->apiKey();
        $model = $this->resolvedModel();

        $imgResponse = Http::timeout(90)->withOptions(['decode_content' => true])->get($sourceUrl);

        if (! $imgResponse->successful()) {
            throw new \RuntimeException('No se pudo descargar la imagen de origen para editar.');
        }

        $body = $imgResponse->body();
        if ($body === '' || strlen($body) > 15 * 1024 * 1024) {
            throw new \RuntimeException('La imagen de origen es inválida o demasiado grande (máx. ~15 MB).');
        }

        $mime = $this->normalizeImageMime((string) $imgResponse->header('Content-Type'), $sourceUrl);
        $b64Input = base64_encode($body);

        $prompt = $this->promptForAction($action);

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mime,
                                'data' => $b64Input,
                            ],
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseModalities' => ['TEXT', 'IMAGE'],
            ],
        ];

        $url = sprintf(self::GEMINI_GENERATE_ENDPOINT, rawurlencode($model));

        try {
            $response = Http::withHeaders([
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(180)
                ->post($url, $payload);
        } catch (\Throwable $e) {
            Log::error('Gemini image AI request failed', ['message' => $e->getMessage()]);

            throw new \RuntimeException('Error de red al llamar a Gemini.', 0, $e);
        }

        if (! $response->successful()) {
            $hint = $this->formatGeminiError($response->json(), $response->body());
            Log::warning('Gemini image AI HTTP error', [
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 800),
            ]);

            throw new \RuntimeException('Gemini rechazó o no pudo procesar la solicitud. '.$hint);
        }

        $json = $response->json();

        foreach ($json['candidates'] ?? [] as $candidate) {
            $finish = $candidate['finishReason'] ?? null;
            if ($finish !== null && $finish !== 'STOP' && $finish !== 'MAX_TOKENS') {
                Log::notice('Gemini candidate finishReason', ['reason' => $finish]);
            }
            foreach ($candidate['content']['parts'] ?? [] as $part) {
                $inline = $part['inlineData'] ?? $part['inline_data'] ?? null;
                if (is_array($inline)) {
                    $data = $inline['data'] ?? '';
                    $outMime = $inline['mimeType'] ?? $inline['mime_type'] ?? (
                        str_contains(strtolower((string) $action), 'background') ? 'image/png' : 'image/jpeg'
                    );
                    if ($data !== '') {
                        return [
                            'processed_base64' => (string) $data,
                            'mime_type' => (string) $outMime,
                            'public_id' => null,
                        ];
                    }
                }
            }
        }

        Log::warning('Gemini returned no inline image data', ['keys' => array_keys(is_array($json) ? $json : [])]);

        throw new \RuntimeException(
            'Gemini no devolvió una imagen. Prueba con otro modelo (p. ej. gemini-2.5-flash-image o gemini-3-pro-image-preview) o revisa el prompt.'
        );
    }

    /** Sin almacén temporal tipo Cloudinary. */
    public function destroyTemp(?string $publicId): void {}

    public function isEnabled(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        return filter_var(SystemSetting::get('image_ai_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
    }

    public function isConfigured(): bool
    {
        return $this->apiKey() !== '';
    }

    public function resolvedModel(): string
    {
        $model = trim((string) SystemSetting::get('gemini_image_model', ''));

        return $model !== '' ? $model : self::DEFAULT_IMAGE_MODEL;
    }

    /**
     * @return list<array{id: string, label: string, description: string}>
     */
    public function availableActions(): array
    {
        return [
            [
                'id' => 'remove_background',
                'label' => 'Quitar fondo',
                'description' => 'Dejar el producto/objeto sobre fondo transparente (PNG).',
            ],
            [
                'id' => 'enhance',
                'label' => 'Mejorar imagen',
                'description' => 'Mejorar luz, color y nitidez sin cambiar encuadre.',
            ],
            [
                'id' => 'studio_white',
                'label' => 'Fondo blanco (estudio)',
                'description' => 'Fondo blanco tipo catálogo con iluminación suave.',
            ],
        ];
    }

    private function apiKey(): string
    {
        return trim((string) SystemSetting::getEncrypted('gemini_api_key', ''));
    }

    private function promptForAction(string $action): string
    {
        return match ($action) {
            'remove_background' => 'Edit this image: remove the entire background completely. Keep only the main subject with clean edges '
                . 'and transparency where the background was (PNG alpha). Do not crop the subject. Preserve realism and proportions.',
            'studio_white' => 'Edit this e-commerce/product photo: remove the original background and place the subject centered on '
                . 'a pure seamless white (#FFFFFF) studio backdrop with soft even catalog lighting. No harsh shadows.',
            default => 'Enhance this image for publication: balanced exposure and white balance, natural colors, moderate sharpening, '
                . 'minimal noise reduction. Keep the framing and composition the same.',
        };
    }

    private function normalizeImageMime(string $headerMime, string $sourceUrl): string
    {
        $headerMime = strtolower(trim(explode(';', $headerMime)[0] ?? ''));

        if (str_starts_with($headerMime, 'image/')) {
            return $headerMime === 'image/jpg' ? 'image/jpeg' : $headerMime;
        }

        $path = parse_url($sourceUrl, PHP_URL_PATH) ?? '';

        return match (true) {
            str_ends_with(strtolower($path), '.png') => 'image/png',
            str_ends_with(strtolower($path), '.webp') => 'image/webp',
            str_ends_with(strtolower((string) $path), '.gif') => 'image/gif',
            default => 'image/jpeg',
        };
    }

    private function formatGeminiError(?array $json, string $rawBody): string
    {
        if ($json === null || $json === []) {
            return trim(substr($rawBody, 0, 200));
        }

        $msg = $json['error']['message'] ?? null;

        return is_string($msg) ? $msg : trim(substr($rawBody, 0, 200));
    }
}
