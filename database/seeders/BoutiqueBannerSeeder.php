<?php

namespace Database\Seeders;

use App\Models\Boutique\BoutiqueBanner;
use App\Support\BoutiqueDemoCatalog;
use Illuminate\Database\Seeder;

class BoutiqueBannerSeeder extends Seeder
{
    public function run(): void
    {
        if (! BoutiqueDemoCatalog::shouldSeedDemoCatalog()) {
            $this->command?->info('BoutiqueBannerSeeder: catálogo real detectado; se omite seed demo.');

            return;
        }

        $banners = [
            [
                'title' => 'Boutique BMW',
                'subtitle' => 'Mercancía oficial, accesorios y estilo de vida BMW',
                'cta_text' => 'Explorar colección',
                'cta_link' => '/boutique/shop',
                'bg_class' => 'slide-1',
                'sort_id' => 1,
                'active' => true,
            ],
            [
                'title' => 'Accesorios Premium',
                'subtitle' => 'Equipa tu BMW con los mejores accesorios originales',
                'cta_text' => 'Ver accesorios',
                'cta_link' => '/boutique/shop',
                'bg_class' => 'slide-2',
                'sort_id' => 2,
                'active' => true,
            ],
            [
                'title' => 'Estilo de Vida',
                'subtitle' => 'Ropa, calzado y artículos de colección BMW',
                'cta_text' => 'Descubrir más',
                'cta_link' => '/boutique/shop',
                'bg_class' => 'slide-3',
                'sort_id' => 3,
                'active' => true,
            ],
        ];

        $created = 0;
        foreach ($banners as $banner) {
            $model = BoutiqueBanner::firstOrCreate(
                ['title' => $banner['title']],
                $banner
            );
            if ($model->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->command?->info("BoutiqueBannerSeeder: {$created} banner(es) demo nuevo(s); resto ya existía.");
    }
}
