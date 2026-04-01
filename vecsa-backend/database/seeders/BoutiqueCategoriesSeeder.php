<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Boutique\BoutiqueCategory;

class BoutiqueCategoriesSeeder extends Seeder
{
    public function run(): void
    {
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

        foreach ($categories as $category) {
            BoutiqueCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        $this->command->info('Categorías de boutique creadas: ' . BoutiqueCategory::count());
    }
}
