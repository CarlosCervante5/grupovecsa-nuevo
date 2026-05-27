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
    /**
     * @param  'vehicle'|'product'  $context
     */
    public function process(string $sourceUrl, string $action, string $context = 'vehicle'): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('No hay llave API de Gemini configurada. Configúrala en Panel desarrollo → Gemini (edición de fotos).');
        }

        $context = $context === 'product' ? 'product' : 'vehicle';

        [$body, $headerMime] = $this->downloadSourceImage($sourceUrl);
        $mime = $this->normalizeImageMime($headerMime, $sourceUrl);
        $b64Input = base64_encode($body);
        $payload = $this->buildGeminiPayload($mime, $b64Input, $action, $context);

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
            ['gemini-3-pro-image-preview'],
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
                'description' => 'Solo fondo blanco y luz suave; sin añadir portaplacas, placas ni accesorios nuevos.',
            ],
        ];
    }

    private function apiKey(): string
    {
        return trim((string) SystemSetting::getEncrypted('gemini_api_key', ''));
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @param  'vehicle'|'product'  $context
     * @return array<string, mixed>
     */
    private function buildGeminiPayload(string $mime, string $b64Input, string $action, string $context): array
    {
        return [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $this->systemInstructionForEdit($context)],
                ],
            ],
            'contents' => [
                [
                    'parts' => [
                        [
                            'inline_data' => [
                                'mime_type' => $mime,
                                'data' => $b64Input,
                            ],
                        ],
                        ['text' => $this->promptForAction($action, $context)],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseModalities' => ['IMAGE'],
                'temperature' => 0.08,
            ],
        ];
    }

    /**
     * @param  'vehicle'|'product'  $context
     */
    private function systemInstructionForEdit(string $context): string
    {
        if ($context === 'product') {
            return <<<'TEXT'
You are a conservative e-commerce product photo retoucher. You edit photographs; you never synthesize a new product.

Hard rules:
- The product in the output must be the same item as in the input photograph.
- Never invent, add, complete, or "fix" parts, labels, tags, packaging, logos, or accessories.
- Do not add price tags, brand labels, extra items, mannequin parts, or props unless they are clearly and fully visible in the input.
- Only background pixels may be replaced. Product pixels stay faithful to the source.
TEXT;
        }

        return <<<'TEXT'
You are a conservative automotive inventory photo retoucher. You edit photographs; you never synthesize a new vehicle.

Hard rules:
- The car in the output must be the same vehicle as in the input photograph.
- Never invent, add, complete, or "fix" parts, accessories, or hardware.
- Never add license plate holders (portaplacas), plate frames, dealer frames, brackets, extra badges, emblems, antennas, spoilers, fog lights, roof racks, stickers, or bumper accessories unless they are clearly and fully visible in the input.
- If the input has no portaplacas, the output must have no portaplacas. If a plate or holder exists, copy it exactly — do not replace or upgrade it.
- Only background pixels may be replaced. Vehicle pixels stay faithful to the source.
TEXT;
    }

    /**
     * @param  'vehicle'|'product'  $context
     */
    private function promptForAction(string $action, string $context): string
    {
        if ($context === 'product') {
            return <<<'PROMPT'
Using the photograph above (the only source of truth), produce ONE edited version.

ALLOWED:
1. Replace the background with pure seamless white (#FFFFFF) studio backdrop.
2. Subtle catalog lighting on the existing product only: light exposure/white-balance correction, soft contact shadow if appropriate. No relighting that changes how the product looks.

ZERO INVENTION — do not add anything not in the photo:
- No new labels, tags, logos, packaging, strings, hangers, mannequin limbs, or extra items.
- No "cleaning up" edges by drawing new product details.
- If something is absent, cropped, or unclear in the input, leave it absent or unclear. Do not guess.

PRESERVE EXACTLY:
- Same product identity, colors, materials, textures, shape, angle, framing, scale, wrinkles, seams, and all visible branding that exists in the input.
- Do not change the product type or generate a different item.

Output: one photorealistic catalog photo. Background white; product unchanged except subtle light correction.
PROMPT;
        }

        return <<<'PROMPT'
Using the photograph above (the only source of truth), produce ONE edited version.

ALLOWED:
1. Replace the background with pure seamless white (#FFFFFF) studio backdrop.
2. Subtle catalog lighting on the existing car only: light exposure/white-balance correction, soft shadow under tires. No relighting that changes how the car looks.

ZERO INVENTION — do not add anything not in the photo:
- No new portaplacas, plate frames, plates, screws, badges, logos, trim, moldings, mirrors, wipers, or body parts.
- No "cleaning up" or "completing" the bumper, grille, or license area by drawing new objects.
- If something is absent, cropped, or unclear in the input, leave it absent or unclear. Do not guess.

PRESERVE EXACTLY:
- Same car identity, color, wheels, tires, glass, interior glimpses, angle, framing, scale, dents, scratches, reflections, and all visible text/plates.
- Do not change model, body shape, paint, or swap components. Do not generate a different vehicle.

Output: one photorealistic catalog photo. Background white; car unchanged except subtle light correction.
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
