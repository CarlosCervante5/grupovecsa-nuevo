<?php

namespace App\Console\Commands;

use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SyncWcImages extends Command
{
    protected $signature = 'sync:wc-images {--csv= : Path to WooCommerce CSV} {--upload : Upload to S3 via Cloudinary} {--dry-run : Show what would be done without doing it}';
    protected $description = 'Sync product images from WooCommerce CSV - downloads from URLs and links to existing products by SKU';

    private $stats = ['linked' => 0, 'skipped' => 0, 'not_found' => 0, 'errors' => 0];

    public function handle()
    {
        $csvPath = $this->option('csv');
        $dryRun = $this->option('dry-run');

        if (!$csvPath) {
            // If no CSV, sync images for products that have WooCommerce URLs as image_path
            $this->syncExistingUrls($dryRun);
            return 0;
        }

        if (!file_exists($csvPath)) {
            $this->error("File not found: $csvPath");
            return 1;
        }

        $this->info("Reading CSV: $csvPath");
        $rows = $this->parseCsv($csvPath);
        $this->info("Found " . count($rows) . " rows");

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row) {
            $sku = trim($row['SKU'] ?? '');
            $images = trim($row['Imágenes'] ?? $row['Images'] ?? '');

            if (!$sku || !$images) {
                $this->stats['skipped']++;
                $bar->advance();
                continue;
            }

            $product = BoutiqueProduct::where('sku', $sku)->first();
            if (!$product) {
                $this->stats['not_found']++;
                $bar->advance();
                continue;
            }

            // Skip if product already has images
            if ($product->images()->count() > 0) {
                $this->stats['skipped']++;
                $bar->advance();
                continue;
            }

            $urls = array_map('trim', explode(',', $images));
            $order = 0;

            foreach ($urls as $url) {
                if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) continue;

                if ($dryRun) {
                    $this->line("  [DRY] Would link: $sku -> $url");
                } else {
                    try {
                        BoutiqueProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $url,
                            'sort_id' => $order++,
                            'status' => 'uploaded',
                        ]);
                        $this->stats['linked']++;
                    } catch (\Exception $e) {
                        $this->stats['errors']++;
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->table(['Metric', 'Count'], [
            ['Images linked', $this->stats['linked']],
            ['Skipped (has images)', $this->stats['skipped']],
            ['Product not found', $this->stats['not_found']],
            ['Errors', $this->stats['errors']],
        ]);

        return 0;
    }

    /**
     * For products that already have WooCommerce URLs as image_path,
     * verify they're still accessible.
     */
    private function syncExistingUrls(bool $dryRun): void
    {
        $this->info("Checking existing product images with external URLs...");

        $images = BoutiqueProductImage::where('image_path', 'like', 'https://vecsaboutique.com%')->get();
        $this->info("Found {$images->count()} images with WooCommerce URLs");

        if ($images->isEmpty()) {
            $this->info("No WooCommerce image URLs found. Use --csv to import from CSV.");
            return;
        }

        $accessible = 0;
        $broken = 0;
        $bar = $this->output->createProgressBar($images->count());
        $bar->start();

        foreach ($images as $image) {
            try {
                $response = Http::timeout(10)->head($image->image_path);
                if ($response->successful()) {
                    $accessible++;
                } else {
                    $broken++;
                    if (!$dryRun) {
                        $this->line("\n  ⚠ Broken: {$image->image_path} (HTTP {$response->status()})");
                    }
                }
            } catch (\Exception $e) {
                $broken++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->table(['Metric', 'Count'], [
            ['Total WC images', $images->count()],
            ['Accessible', $accessible],
            ['Broken/Unreachable', $broken],
        ]);
    }

    private function parseCsv(string $file): array
    {
        $rows = [];
        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        if ($headers) $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $headers = array_map('trim', $headers);

        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($data) >= count($headers)) {
                $rows[] = array_combine($headers, array_slice($data, 0, count($headers)));
            }
        }
        fclose($handle);
        return $rows;
    }
}
