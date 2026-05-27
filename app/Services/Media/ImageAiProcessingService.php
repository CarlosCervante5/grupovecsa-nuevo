<?php

namespace App\Services\Media;

use App\Models\SystemSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Edición de imágenes con IA usando la API Gemini (modelos imagen / Nano Banana).
 */
final class ImageAiProcessingService
{
    private const GEMINI_GENERATE_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    /** Modelo estable recomendado por Google para edición imagen + texto. */
    public const DEFAULT_IMAGE_MODEL = 'gemini-2.5-flash-image';

    /** Modelos de respaldo si el configurado no existe en la cuenta/región. */
    private const FALLBACK_IMAGE_MODELS = [
        'gemini-2.5-flash-image-preview',
        'gemini-3-pro-image-preview',
    ];

    /**
     * @return array{processed_base64: string, mime_type: string, public_id: null}
     */
    public function process(string $sourceUrl, string $action): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('No hay llave API de Gemini configurada. Configúrala en Panel desarrollo → Gemini (edición de fotos).');
        }

        [$body, $headerMime] = $this->downloadSourceImage($sourceUrl);
        $mime = $this->normalizeImageMime($headerMime, $sourceUrl);
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
                'responseModalities' => ['IMAGE'],
                'temperature' => 0.15,
            ],
        ];

        $lastHint = '';
        foreach ($this->modelCandidates() as $model) {
            try {
                $response = $this->callGemini($model, $payload);
            } catch (ConnectionException $e) {
                Log::error('Gemini image AI connection failed', ['model' => $model, 'message' => $e->getMessage()]);

                throw new \RuntimeException(
                    'No se pudo conectar con la API de Gemini. Comprueba conectividad del servidor o firewall.',
                    0,
                    $e
                );
            }

            if (in_array($response->status(), [401, 403], true)) {
                throw new \RuntimeException(
                    'La llave API de Gemini fue rechazada (HTTP '.$response->status().'). Revisa la llave en Panel desarrollo.'
                );
            }

            if ($response->status() === 404) {
                $lastHint = $this->formatGeminiError($response->json(), $response->body());
                Log::notice('Gemini model not found, trying fallback', ['model' => $model, 'hint' => $lastHint]);

                continue;
            }

            if (! $response->successful()) {
                $lastHint = $this->formatGeminiError($response->json(), $response->body());
                Log::warning('Gemini image AI HTTP error', [
                    'model' => $model,
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 800),
                ]);

                throw new \RuntimeException('Gemini rechazó o no pudo procesar la solicitud. '.$lastHint);
            }

            try {
                return $this->parseImageFromGeminiResponse($response->json(), $action);
            } catch (\RuntimeException $e) {
                $lastHint = $e->getMessage();
                Log::warning('Gemini response without image', ['model' => $model, 'hint' => $lastHint]);

                continue;
            }
        }

        throw new \RuntimeException(
            'Ningún modelo Gemini de imagen respondió correctamente. '
            .($lastHint !== '' ? $lastHint : 'Prueba gemini-2.5-flash-image en Panel desarrollo.')
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
        $key = $this->apiKey();

        return $key !== '' && strlen($key) >= 20;
    }

    public function resolvedModel(): string
    {
        $model = trim((string) SystemSetting::get('gemini_image_model', ''));

        return $model !== '' ? $model : self::DEFAULT_IMAGE_MODEL;
    }

    /**
     * @return list<string>
     */
    private function modelCandidates(): array
    {
        $candidates = array_merge(
            [$this->resolvedModel()],
            [self::DEFAULT_IMAGE_MODEL],
            self::FALLBACK_IMAGE_MODELS,
        );

        $unique = [];
        foreach ($candidates as $model) {
            $model = trim($model);
            if ($model === '' || ! preg_match('/^[a-zA-Z0-9._\-]+$/', $model)) {
                continue;
            }
            if (! in_array($model, $unique, true)) {
                $unique[] = $model;
            }
        }

        return $unique;
    }

    /**
     * @return array{0: string, 1: string} [body, content-type header]
     */
    private function downloadSourceImage(string $sourceUrl): array
    {
        try {
            $imgResponse = Http::timeout(90)
                ->withOptions(['decode_content' => true])
                ->get($sourceUrl);
        } catch (ConnectionException $e) {
            throw new \RuntimeException(
                'No se pudo descargar la imagen de origen (error de red). Verifica que la URL sea pública.',
                0,
                $e
            );
        }

        if (! $imgResponse->successful()) {
            throw new \RuntimeException(
                'No se pudo descargar la imagen de origen (HTTP '.$imgResponse->status().').'
            );
        }

        $body = $imgResponse->body();
        if ($body === '' || strlen($body) > 15 * 1024 * 1024) {
            throw new \RuntimeException('La imagen de origen es inválida o demasiado grande (máx. ~15 MB).');
        }

        return [$body, (string) $imgResponse->header('Content-Type')];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function callGemini(string $model, array $payload): Response
    {
        $url = sprintf(self::GEMINI_GENERATE_ENDPOINT, rawurlencode($model));

        return Http::withHeaders([
            'x-goog-api-key' => $this->apiKey(),
            'Content-Type' => 'application/json',
        ])
            ->timeout(180)
            ->post($url, $payload);
    }

    /**
     * @param  array<string, mixed>|null  $json
     * @return array{processed_base64: string, mime_type: string, public_id: null}
     */
    private function parseImageFromGeminiResponse(?array $json, string $action): array
    {
        if (! is_array($json)) {
            throw new \RuntimeException('Respuesta de Gemini no es JSON válido.');
        }

        $feedback = $json['promptFeedback'] ?? null;
        if (is_array($feedback)) {
            $block = $feedback['blockReason'] ?? null;
            if ($block !== null && $block !== '') {
                throw new \RuntimeException('Gemini bloqueó la solicitud: '.$block);
            }
        }

        foreach ($json['candidates'] ?? [] as $candidate) {
            $finish = $candidate['finishReason'] ?? null;
            if ($finish !== null && ! in_array($finish, ['STOP', 'MAX_TOKENS'], true)) {
                Log::notice('Gemini candidate finishReason', ['reason' => $finish]);
            }
            foreach ($candidate['content']['parts'] ?? [] as $part) {
                $inline = $part['inlineData'] ?? $part['inline_data'] ?? null;
                if (! is_array($inline)) {
                    continue;
                }
                $data = $inline['data'] ?? '';
                if ($data === '') {
                    continue;
                }
                $outMime = $inline['mimeType'] ?? $inline['mime_type'] ?? 'image/jpeg';

                return [
                    'processed_base64' => (string) $data,
                    'mime_type' => (string) $outMime,
                    'public_id' => null,
                ];
            }
        }

        throw new \RuntimeException(
            'Gemini no devolvió datos de imagen. Prueba el modelo gemini-2.5-flash-image en Panel desarrollo.'
        );
    }

    /**
     * @return list<array{id: string, label: string, description: string}>
     */
    public function availableActions(): array
    {
        return [
            [
                'id' => 'studio_white',
                'label' => 'Fondo blanco (estudio)',
                'description' => 'Quita el fondo y ajusta la luz; el vehículo se mantiene igual que en la foto original.',
            ],
        ];
    }

    private function apiKey(): string
    {
        return trim((string) SystemSetting::getEncrypted('gemini_api_key', ''));
    }

    private function promptForAction(string $action): string
    {
        return <<<'PROMPT'
Edit this existing dealership vehicle inventory photograph. This is a strict photo edit of the SAME car, not a new image.

ALLOWED CHANGES ONLY:
1. Remove the original background completely and replace it with a pure seamless white (#FFFFFF) studio backdrop.
2. Apply subtle catalog lighting on the existing vehicle only: gentle exposure balance, neutral white balance, soft shadow under the tires. No dramatic relighting.

STRICT PRESERVATION — the vehicle must remain pixel-faithful and identical to the input:
- Same exact car: make, model, body style, year look, paint color, trim, wheels, tires, badges, logos, grille, lights, mirrors, glass, interior visible through windows.
- Same camera angle, perspective, framing, scale, and position of the car in the frame. Do not crop, zoom, or reframe the vehicle.
- Keep all license plates, text, decals, scratches, dents, panel gaps, reflections, and details that belong to this specific car.
- Do NOT replace, redesign, upgrade, stylize, or "improve" the vehicle. Do NOT generate a different car or change its color, wheels, or body shape.
- Do NOT add or remove parts, accessories, people, or objects touching the car.

FORBIDDEN: changing the car model, changing paint color, swapping wheels, merging background into the body, cartoon or AI-art look, blur on vehicle details, hallucinated parts.

Output one photorealistic edited photo suitable for an automotive sales catalog.
PROMPT;
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
