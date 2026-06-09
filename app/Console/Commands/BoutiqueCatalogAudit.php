<?php

namespace App\Console\Commands;

use App\Models\Boutique\BoutiqueBanner;
use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductImage;
use App\Support\BoutiqueImageUrlClassifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class BoutiqueCatalogAudit extends Command
{
    protected $signature = 'boutique:catalog-audit
                            {--require-cdn-only : Falla si quedan URLs WordPress u otras no-CDN en imágenes de catálogo}
                            {--show-samples=5 : Cuántas URLs de ejemplo mostrar por categoría (0 = ninguna)}';

    protected $description = 'Audita URLs de imágenes del catálogo boutique (CloudFront, Cloudinary, WordPress, otras)';

    public function handle(): int
    {
        if (! Schema::hasTable((string) env('DB_TABLE_PREFIX', '').'boutique_products')) {
            $this->error('No existe la tabla boutique_products en esta base de datos.');

            return self::FAILURE;
        }

        $samples = max(0, (int) $this->option('show-samples'));
        $counts = [
            BoutiqueImageUrlClassifier::CLOUDFRONT => 0,
            BoutiqueImageUrlClassifier::CLOUDINARY => 0,
            BoutiqueImageUrlClassifier::WORDPRESS => 0,
            BoutiqueImageUrlClassifier::OTHER => 0,
            BoutiqueImageUrlClassifier::EMPTY => 0,
        ];
        $samplesByType = array_fill_keys(array_keys($counts), []);

        foreach (BoutiqueProductImage::query()->select(['id', 'product_id', 'image_path', 'status'])->cursor() as $image) {
            $type = BoutiqueImageUrlClassifier::classify($image->image_path);
            $counts[$type]++;
            if ($samples > 0 && count($samplesByType[$type]) < $samples) {
                $samplesByType[$type][] = "#{$image->id} status={$image->status} → {$image->image_path}";
            }
        }

        $bannerCounts = [
            BoutiqueImageUrlClassifier::CLOUDFRONT => 0,
            BoutiqueImageUrlClassifier::CLOUDINARY => 0,
            BoutiqueImageUrlClassifier::WORDPRESS => 0,
            BoutiqueImageUrlClassifier::OTHER => 0,
            BoutiqueImageUrlClassifier::EMPTY => 0,
        ];

        if (Schema::hasTable((string) env('DB_TABLE_PREFIX', '').'boutique_banners')) {
            foreach (BoutiqueBanner::query()->select(['id', 'title', 'desktop_image_path', 'mobile_image_path'])->cursor() as $banner) {
                foreach (['desktop_image_path', 'mobile_image_path'] as $field) {
                    $type = BoutiqueImageUrlClassifier::classify($banner->{$field});
                    $bannerCounts[$type]++;
                }
            }
        }

        $productTotal = BoutiqueProduct::query()->count();
        $imageTotal = array_sum($counts);
        $cdnTotal = $counts[BoutiqueImageUrlClassifier::CLOUDFRONT] + $counts[BoutiqueImageUrlClassifier::CLOUDINARY];
        $wpTotal = $counts[BoutiqueImageUrlClassifier::WORDPRESS];

        $this->info('Auditoría de catálogo boutique');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Productos', $productTotal],
                ['Imágenes de producto', $imageTotal],
                ['CloudFront', $counts[BoutiqueImageUrlClassifier::CLOUDFRONT]],
                ['Cloudinary', $counts[BoutiqueImageUrlClassifier::CLOUDINARY]],
                ['WordPress', $wpTotal],
                ['Otras / externas', $counts[BoutiqueImageUrlClassifier::OTHER]],
                ['Vacías', $counts[BoutiqueImageUrlClassifier::EMPTY]],
                ['CDN (CF + Cloudinary)', $cdnTotal],
            ]
        );

        if (array_sum($bannerCounts) > 0) {
            $this->comment('Banners (desktop + mobile):');
            $this->table(
                ['Tipo', 'Cantidad'],
                [
                    ['CloudFront', $bannerCounts[BoutiqueImageUrlClassifier::CLOUDFRONT]],
                    ['Cloudinary', $bannerCounts[BoutiqueImageUrlClassifier::CLOUDINARY]],
                    ['WordPress', $bannerCounts[BoutiqueImageUrlClassifier::WORDPRESS]],
                    ['Otras', $bannerCounts[BoutiqueImageUrlClassifier::OTHER]],
                    ['Vacías', $bannerCounts[BoutiqueImageUrlClassifier::EMPTY]],
                ]
            );
        }

        if ($samples > 0) {
            foreach ($samplesByType as $type => $lines) {
                if ($lines === []) {
                    continue;
                }
                $this->newLine();
                $this->line("Ejemplos {$type}:");
                foreach ($lines as $line) {
                    $this->line("  - {$line}");
                }
            }
        }

        $blocking = $wpTotal
            + $counts[BoutiqueImageUrlClassifier::OTHER]
            + $bannerCounts[BoutiqueImageUrlClassifier::WORDPRESS]
            + $bannerCounts[BoutiqueImageUrlClassifier::OTHER];

        if ((bool) $this->option('require-cdn-only')) {
            if ($blocking > 0) {
                $this->error("Bloqueado: quedan {$blocking} URL(s) no-CDN en productos/banners.");
                $this->line('Migra WordPress con: php artisan boutique:migrate-wp-images --limit=0 --sleep=1');
                $this->line('Vuelve a auditar con: php artisan boutique:catalog-audit --require-cdn-only');

                return self::FAILURE;
            }

            $this->info('OK: todas las imágenes de catálogo usan CloudFront o Cloudinary.');
        } elseif ($wpTotal > 0) {
            $this->warn("Quedan {$wpTotal} imagen(es) WordPress. Migra antes del dump a producción si no quieres depender de WP.");
        }

        return self::SUCCESS;
    }
}
