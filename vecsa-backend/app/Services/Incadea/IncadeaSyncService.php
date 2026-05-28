<?php

namespace App\Services\Incadea;

use App\Models\Boutique\BoutiqueProduct;
use App\Services\Boutique\BoutiqueProductPublicationService;
use App\Models\Boutique\IncadeaSyncLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class IncadeaSyncService
{
    public const DEFAULT_API_URL = 'http://52.21.121.207/api/incadea/get_spare_parts';

    protected CategoryMapper $categoryMapper;

    public function __construct(CategoryMapper $categoryMapper)
    {
        $this->categoryMapper = $categoryMapper;
    }

    /**
     * URL efectiva para GET a Incadea (normaliza env vacío y rutas incompletas).
     */
    public static function resolveIncadeaApiUrl(): string
    {
        $url = config('services.incadea.api_url');
        if (! is_string($url) || trim($url) === '') {
            return self::DEFAULT_API_URL;
        }

        $url = rtrim(trim($url), '/');

        if (preg_match('#/api/incadea$#i', $url)) {
            return $url.'/get_spare_parts';
        }

        return $url;
    }

    public static function maskUrlForDisplay(string $url): string
    {
        $parsed = parse_url($url);
        if (! is_array($parsed) || ! isset($parsed['host'])) {
            return $url;
        }

        $scheme = $parsed['scheme'] ?? 'http';
        $path = $parsed['path'] ?? '';

        return $scheme.'://'.$parsed['host'].$path;
    }

    /**
     * Comprueba conectividad desde el servidor actual (p. ej. Railway sandbox).
     *
     * @return array{endpoint: string, http_status: int|null, ok: bool, has_spare_parts?: bool, error?: string, hint?: string}
     */
    public static function probeApi(): array
    {
        $url = self::resolveIncadeaApiUrl();
        $endpoint = self::maskUrlForDisplay($url);

        try {
            $response = Http::timeout(15)->get($url);
            $status = $response->status();
            $ok = $response->successful();
            $hasParts = is_array($response->json('data.spare_parts'));

            $result = [
                'endpoint' => $endpoint,
                'http_status' => $status,
                'ok' => $ok && $hasParts,
                'has_spare_parts' => $hasParts,
            ];

            if (! $ok) {
                $result['hint'] = self::hintForHttpStatus($status, $endpoint);
            } elseif (! $hasParts) {
                $result['hint'] = 'La API respondió pero el JSON no incluye data.spare_parts.';
            }

            return $result;
        } catch (\Throwable $e) {
            return [
                'endpoint' => $endpoint,
                'http_status' => null,
                'ok' => false,
                'error' => $e->getMessage(),
                'hint' => 'No se pudo conectar a Incadea desde este servidor. Revisa INCADEA_API_URL (debe ser HTTP, no HTTPS) y firewall.',
            ];
        }
    }

    private static function hintForHttpStatus(int $status, string $endpoint): string
    {
        if ($status === 404) {
            return 'HTTP 404 en '.$endpoint.'. En Railway usa exactamente: '.self::DEFAULT_API_URL;
        }

        return 'Incadea respondió HTTP '.$status.' en '.$endpoint.'.';
    }

    /**
     * Fetch spare parts from the Incadea API.
     */
    public function fetchSpareParts(): array
    {
        $url = self::resolveIncadeaApiUrl();
        $response = $this->requestSpareParts($url);

        if (! $response->successful()) {
            $displayUrl = self::maskUrlForDisplay($url);
            Log::error('INCADEA_FETCH_FAILED', [
                'http_status' => $response->status(),
                'endpoint' => $displayUrl,
                'body_preview' => substr($response->body(), 0, 300),
            ]);

            throw new \RuntimeException(
                'Incadea API respondió HTTP '.$response->status().' al solicitar '.$displayUrl.'. '.
                self::hintForHttpStatus($response->status(), $displayUrl)
            );
        }

        $raw = $response->json('data.spare_parts');
        if (is_object($raw)) {
            $raw = json_decode(json_encode($raw), true);
        }
        if (! is_array($raw)) {
            Log::warning('INCADEA_UNEXPECTED_PAYLOAD', [
                'body_preview' => substr($response->body(), 0, 500),
            ]);
            throw new \RuntimeException(
                'Incadea API: la respuesta no contiene un arreglo data.spare_parts. Revisa el formato del servicio externo.'
            );
        }

        return $raw;
    }

    /**
     * GET con reintento en ruta alternativa si la configurada devuelve 404.
     */
    private function requestSpareParts(string $url): \Illuminate\Http\Client\Response
    {
        $response = Http::timeout(30)->get($url);

        if ($response->status() !== 404) {
            return $response;
        }

        $alternates = [];
        if (preg_match('#/get_spare_parts$#i', $url)) {
            $alternates[] = preg_replace('#/get_spare_parts$#i', '/spare_parts', $url);
        }
        if (preg_match('#/api/incadea$#i', $url)) {
            $alternates[] = $url.'/get_spare_parts';
        }

        foreach (array_unique(array_filter($alternates)) as $alt) {
            if ($alt === $url) {
                continue;
            }
            $retry = Http::timeout(30)->get($alt);
            if ($retry->successful()) {
                return $retry;
            }
        }

        return $response;
    }

    /**
     * Filter parts by excluded brands and categories.
     */
    public function filterParts(array $parts, array $filters): array
    {
        $excludedBrands = $filters['excluded_brands'] ?? [];
        $excludedCategories = $filters['excluded_categories'] ?? [];
        if (! is_array($excludedBrands)) {
            $excludedBrands = [];
        }
        if (! is_array($excludedCategories)) {
            $excludedCategories = [];
        }

        return array_values(array_filter($parts, function ($part) use ($excludedBrands, $excludedCategories) {
            if (in_array($part['brand'] ?? '', $excludedBrands)) {
                return false;
            }
            if (in_array($part['category'] ?? '', $excludedCategories)) {
                return false;
            }
            return true;
        }));
    }

    /**
     * Sync a single spare part to BoutiqueProduct.
     * Returns 'created', 'updated', or 'skipped'.
     */
    public function syncPart(array $part): string
    {
        $part = is_array($part) ? $part : (array) $part;
        $categoryId = $this->categoryMapper->resolve((string) ($part['category'] ?? ''));

        if ($categoryId === null) {
            return 'skipped';
        }

        $hasStock = ((int) ($part['exists_parts'] ?? 0)) > 0;

        $existing = BoutiqueProduct::where('sku', $part['no_part'])->first();

        $canPublish = $hasStock && (
            $existing
                ? BoutiqueProductPublicationService::hasPublishableImage($existing)
                : false
        );

        $productData = [
            'category_id' => $categoryId,
            'name'        => $part['description'] ?? '',
            'description' => "Marca: {$part['brand']} | Ubicación: {$part['location_code']} | Caja: {$part['box_code']}",
            'price'       => $part['unit_price'] ?? 0,
            'stock'       => (int) ($part['exists_parts'] ?? 0),
            'active'      => $canPublish,
        ];

        if (!$existing) {
            $product = new BoutiqueProduct(array_merge($productData, ['sku' => $part['no_part']]));
            $product->uuid = (string) Uuid::uuid4();
            $product->save();
            return 'created';
        }

        // Check for real changes
        $hasChanges = false;
        foreach ($productData as $key => $value) {
            if ($existing->{$key} != $value) {
                $hasChanges = true;
                break;
            }
        }

        if ($hasChanges) {
            $existing->update($productData);
            return 'updated';
        }

        return 'skipped';
    }

    /**
     * Execute the full sync process.
     */
    public function executeSyncProcess(array $filters): array
    {
        // Try to create log, but don't fail if table doesn't exist
        $log = null;
        try {
            $log = IncadeaSyncLog::create([
                'user_id'         => auth()->id(),
                'status'          => 'running',
                'started_at'      => now(),
                'filters_applied' => $filters,
            ]);
        } catch (\Throwable $e) {
            // Log table may not exist yet — continue without logging
        }

        try {
            $spareParts = $this->fetchSpareParts();
            if ($log) $log->update(['total_fetched' => count($spareParts)]);

            $filtered = $this->filterParts($spareParts, $filters);

            $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];
            $errorDetails = [];

            foreach ($filtered as $part) {
                try {
                    $result = $this->syncPart($part);
                    $stats[$result]++;
                } catch (\Throwable $e) {
                    $stats['errors']++;
                    $errorDetails[] = [
                        'no_part' => is_array($part) ? ($part['no_part'] ?? 'unknown') : 'unknown',
                        'error'   => $e->getMessage(),
                    ];
                }
            }

            if ($log) {
                $log->update([
                    'status'         => 'completed',
                    'total_created'  => $stats['created'],
                    'total_updated'  => $stats['updated'],
                    'total_skipped'  => $stats['skipped'],
                    'total_errors'   => $stats['errors'],
                    'error_details'  => $errorDetails ?: null,
                    'finished_at'    => now(),
                ]);
            }

            return [
                'total_fetched'    => count($spareParts),
                'total_filtered'   => count($filtered),
                'created'          => $stats['created'],
                'updated'          => $stats['updated'],
                'skipped'          => $stats['skipped'],
                'errors'           => $stats['errors'],
                'duration_seconds' => 0,
                'log_uuid'         => $log ? $log->uuid : null,
            ];

        } catch (\Throwable $e) {
            if ($log) {
                try {
                    $log->update([
                        'status'        => 'failed',
                        'error_details' => [['error' => $e->getMessage()]],
                        'finished_at'   => now(),
                    ]);
                } catch (\Throwable $ignored) {
                }
            }
            throw $e;
        }
    }

    /**
     * Get the default sync config.
     */
    public static function getDefaultConfig(): array
    {
        return [
            'excluded_brands'     => ['OTRAS'],
            'excluded_categories' => ['Unknown category'],
        ];
    }

    /**
     * Read sync config from system_settings.
     */
    public static function getSyncConfig(): array
    {
        try {
            $raw = SystemSetting::get('incadea_sync_config');

            if ($raw === null) {
                return self::getDefaultConfig();
            }

            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : self::getDefaultConfig();
        } catch (\Exception $e) {
            return self::getDefaultConfig();
        }
    }

    /**
     * Write sync config to system_settings.
     */
    public static function setSyncConfig(array $config): void
    {
        SystemSetting::set('incadea_sync_config', json_encode($config));
    }
}
