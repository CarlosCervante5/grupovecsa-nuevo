<?php

namespace Database\Seeders;

use App\Models\Boutique\BoutiqueCategory;
use App\Support\BoutiqueDemoCatalog;
use Illuminate\Database\Seeder;

class BoutiqueCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        if (! BoutiqueDemoCatalog::shouldSeedDemoCatalog()) {
            $this->command?->info('BoutiqueCategoriesSeeder: catálogo real detectado; se omite seed demo.');

            return;
        }

        $categories = [
            [
                'name'        => 'Accesorios',
                'description' => 'Accesorios para tu vehículo',
                'active'      => true,
            ],
            [
                'name'        => 'Clean & Care',
                'description' => 'Productos de limpieza y cuidado',
                'active'      => true,
            ],
            [
                'name'        => 'Life Style',
                'description' => 'Ropa, calzado y artículos de estilo de vida',
                'active'      => true,
            ],
            [
                'name'        => 'Llantas y Rines',
                'description' => 'Llantas, rines y accesorios de rodadura',
                'active'      => true,
            ],
            [
                'name'        => 'Rider G&G',
                'description' => 'Equipo y accesorios para motociclistas',
                'active'      => true,
            ],
        ];

        $created = 0;
        foreach ($categories as $category) {
            $model = BoutiqueCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
            if ($model->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->command?->info("BoutiqueCategoriesSeeder: {$created} categoría(s) demo nueva(s); resto ya existía.");
    }
}
