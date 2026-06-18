<?php

namespace App\Console\Commands;

use App\Models\Boutique\BoutiqueProductImage;
use App\Services\Media\CloudinaryImageStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MigrateBoutiqueWpImages extends Command
{
    private const MAX_BYTES = 15 * 1024 * 1024;

    private const DEFAULT_WP_PREFIX = 'https://vecsaboutique.com';

    protected $signature = 'boutique:migrate-wp-images
                            {--dry-run : List targets without downloading or uploading}
                            {--limit=50 : Max images to process in this run (0 = no limit)}
                            {--sku= : Only images for a product with this SKU}
                            {--from-id=0 : Process images with id greater than this value}
                            {--sleep=0 : Seconds to wait between images (rate limiting)}
                            {--prefix= : URL prefix to match (default: https://vecsaboutique.com)}';

    protected $description = 'Migrate boutique product images from WordPress URLs to Cloudinary/S3 storage';

    /** @var array{migrated: int, failed: int, skipped: int, listed: int} */
    private array $stats = [
        'migrated' => 0,
        'failed' => 0,
        'skipped' => 0,
        'listed' => 0,
    ];

    public function handle(CloudinaryImageStorageService $storage): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(0, (int) $this->option('limit'));
        $fromId = max(0, (int) $this->option('from-id'));
        $sleep = max(0, (int) $this->option('sleep'));
        $sku = trim((string) $this->option('sku'));
        $prefix = rtrim(trim((string) $this->option('prefix')) ?: self::DEFAULT_WP_PREFIX, '/').'%';

        $query = BoutiqueProductImage::query()
            ->with('product')
            ->where('image_path', 'like', $prefix)
            ->where('id', '>', $fromId)
            ->orderBy('id');

        if ($sku !== '') {
            $query->whereHas('product', fn ($q) => $q->where('sku', $sku));
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('No WordPress boutique images found for the given filters.');

            return self::SUCCESS;
        }

        $batch = $limit > 0 ? $limit : $total;
        $this->info(sprintf(
            'Found %d WordPress image(s). Processing up to %d (dry-run: %s).',
            $total,
            min($batch, $total),
            $dryRun ? 'yes' : 'no'
        ));

        $images = $query->limit($batch)->get();
        $bar = $this->output->createProgressBar($images->count());
        $bar->start();

        foreach ($images as $image) {
            $this->processImage($image, $storage, $dryRun);
            $bar->advance();

            if ($sleep > 0) {
                sleep($sleep);
            }
        }

        $bar->finish();
        $this->newLine(2);

        $rows = [
            ['Migrated', $this->stats['migrated']],
            ['Failed', $this->stats['failed']],
            ['Skipped', $this->stats['skipped']],
        ];
        if ($dryRun) {
            $rows[] = ['Listed (dry-run)', $this->stats['listed']];
        } else {
            $rows[] = [
                'Remaining (approx.)',
                max(0, $total - $this->stats['migrated'] - $this->stats['failed'] - $this->stats['skipped']),
            ];
        }
        $this->table(['Metric', 'Count'], $rows);

        if (! $dryRun && $this->stats['failed'] > 0) {
            $this->warn('Some images failed. Re-run the command; only WordPress URLs are selected.');

            return self::FAILURE;
        }

        $remaining = BoutiqueProductImage::query()
            ->where('image_path', 'like', $prefix)
            ->count();

        if ($remaining > 0 && ! $dryRun) {
            $this->comment("Still {$remaining} WordPress image(s) left. Re-run with --from-id={$images->last()?->id} or without --from-id.");
        } elseif ($remaining === 0) {
            $this->info('All matching WordPress images have been migrated.');
        }

        return self::SUCCESS;
    }

    private function processImage(
        BoutiqueProductImage $image,
        CloudinaryImageStorageService $storage,
        bool $dryRun
    ): void {
        $product = $image->product;
        $sku = $product?->sku ?? '—';
        $oldUrl = (string) $image->image_path;

        if ($product === null || $product->uuid === null || $product->uuid === '') {
            $this->stats['skipped']++;
            $this->line("\n  ⊘ Skip image #{$image->id}: product missing");

            return;
        }

        if ($dryRun) {
            $this->line("\n  [DRY] #{$image->id} SKU {$sku} → {$oldUrl}");
            $this->stats['listed']++;

            return;
        }

        try {
            $download = $this->downloadImage($oldUrl);
            if (! $download['success']) {
                throw new \RuntimeException($download['message']);
            }

            $body = $download['data']['body'];
            $extension = $this->detectImageExtension($body, $download['data']['content_type'], $oldUrl);
            $suffix = 'wp_'.$image->id;

            $stored = $storage->storeBoutiqueImageBinary(
                $product->uuid,
                $body,
                $extension,
                $suffix
            );

            $image->update([
                'image_path' => $stored['url'],
                'cloudinary_public_id' => $stored['public_id'],
                'status' => 'uploaded',
            ]);

            $this->stats['migrated']++;
            Log::info('boutique:migrate-wp-images OK', [
                'image_id' => $image->id,
                'sku' => $sku,
                'old_url' => $oldUrl,
                'new_url' => $stored['url'],
            ]);
        } catch (\Throwable $e) {
            $this->stats['failed']++;
            $this->line("\n  ✗ #{$image->id} SKU {$sku}: ".$e->getMessage());
            Log::warning('boutique:migrate-wp-images failed', [
                'image_id' => $image->id,
                'sku' => $sku,
                'url' => $oldUrl,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return array{success: bool, message?: string, data?: array{body: string, content_type: string|null}}
     */
    private function downloadImage(string $url): array
    {
        $response = Http::timeout(90)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; GrupoVECSA-Boutique-WpMigrate/1.0)',
            ])
            ->get($url);

        if (! $response->successful()) {
            return [
                'success' => false,
                'message' => 'HTTP '.$response->status().' al descargar la imagen',
            ];
        }

        $contentType = strtolower((string) $response->header('Content-Type'));
        $body = (string) $response->body();

        if ($this->looksLikeHtml($contentType, $body)) {
            return [
                'success' => false,
                'message' => 'La URL devolvió HTML en lugar de una imagen',
            ];
        }

        if (strlen($body) > self::MAX_BYTES) {
            return [
                'success' => false,
                'message' => 'La imagen supera el límite de 15 MB',
            ];
        }

        if (! $this->isImageBinary($body)) {
            return [
                'success' => false,
                'message' => 'El archivo descargado no es una imagen válida',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'body' => $body,
                'content_type' => $contentType !== '' ? $contentType : null,
            ],
        ];
    }

    private function looksLikeHtml(string $contentType, string $body): bool
    {
        if (str_contains($contentType, 'text/html')) {
            return true;
        }

        $start = ltrim(substr($body, 0, 200));

        return str_starts_with(strtolower($start), '<!doctype')
            || str_starts_with(strtolower($start), '<html');
    }

    private function isImageBinary(string $body): bool
    {
        if ($body === '') {
            return false;
        }

        return @getimagesizefromstring($body) !== false;
    }

    private function detectImageExtension(string $body, ?string $contentType, string $url): string
    {
        $info = @getimagesizefromstring($body);
        if ($info !== false && isset($info[2])) {
            return match ($info[2]) {
                IMAGETYPE_PNG => 'png',
                IMAGETYPE_GIF => 'gif',
                IMAGETYPE_WEBP => 'webp',
                default => 'jpg',
            };
        }

        if ($contentType !== null) {
            if (str_contains($contentType, 'png')) {
                return 'png';
            }
            if (str_contains($contentType, 'gif')) {
                return 'gif';
            }
            if (str_contains($contentType, 'webp')) {
                return 'webp';
            }
            if (str_contains($contentType, 'avif')) {
                return 'jpg';
            }
        }

        $path = strtolower(parse_url($url, PHP_URL_PATH) ?? '');
        if (str_ends_with($path, '.png')) {
            return 'png';
        }
        if (str_ends_with($path, '.gif')) {
            return 'gif';
        }
        if (str_ends_with($path, '.webp') || str_ends_with($path, '.avif')) {
            return 'jpg';
        }

        return 'jpg';
    }
}
