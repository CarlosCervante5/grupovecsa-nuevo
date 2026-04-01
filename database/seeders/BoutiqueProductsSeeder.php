<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Boutique\BoutiqueCategory;
use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductVariant;

class BoutiqueProductsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Simple products (no variants) ─────────────────────────────────────
        $simpleProducts = [
            // Accesorios
            ['category' => 'Accesorios', 'name' => 'Soporte de celular para moto',   'description' => 'Soporte universal resistente al agua para manubrio.',          'price' => 599.00,   'sku' => 'ACC-001', 'stock' => 20],
            ['category' => 'Accesorios', 'name' => 'Cargador USB para moto',          'description' => 'Cargador dual USB resistente a la intemperie.',                 'price' => 449.00,   'sku' => 'ACC-002', 'stock' => 15],
            ['category' => 'Accesorios', 'name' => 'Espejo retrovisor deportivo',     'description' => 'Par de espejos ajustables con acabado negro mate.',             'price' => 890.00,   'sku' => 'ACC-003', 'stock' => 10],
            ['category' => 'Accesorios', 'name' => 'Alforja lateral impermeable',     'description' => 'Bolsa lateral de 20L con cierre impermeable.',                  'price' => 1299.00,  'sku' => 'ACC-004', 'stock' => 8],
            // Clean & Care
            ['category' => 'Clean & Care', 'name' => 'Kit de limpieza BMW',           'description' => 'Set completo con shampoo, cera y microfibra.',                  'price' => 750.00,   'sku' => 'CLN-001', 'stock' => 25],
            ['category' => 'Clean & Care', 'name' => 'Cera protectora premium',       'description' => 'Cera de carnauba con protección UV de larga duración.',         'price' => 480.00,   'sku' => 'CLN-002', 'stock' => 30],
            ['category' => 'Clean & Care', 'name' => 'Limpiador de cadena',           'description' => 'Spray desengrasante para cadena y transmisión.',                'price' => 220.00,   'sku' => 'CLN-003', 'stock' => 40],
            ['category' => 'Clean & Care', 'name' => 'Lubricante de cadena',          'description' => 'Lubricante de alta adherencia para todo clima.',                'price' => 195.00,   'sku' => 'CLN-004', 'stock' => 35],
            // Llantas y Rines
            ['category' => 'Llantas y Rines', 'name' => 'Llanta Michelin Pilot Road 5', 'description' => 'Llanta trasera 180/55 ZR17 para uso mixto.',                 'price' => 4200.00,  'sku' => 'LLR-001', 'stock' => 6],
            ['category' => 'Llantas y Rines', 'name' => 'Llanta Pirelli Angel GT',    'description' => 'Llanta delantera 120/70 ZR17 touring.',                        'price' => 3600.00,  'sku' => 'LLR-002', 'stock' => 6],
            ['category' => 'Llantas y Rines', 'name' => 'Rin forjado 17" negro',      'description' => 'Rin de aluminio forjado acabado negro mate.',                  'price' => 8500.00,  'sku' => 'LLR-003', 'stock' => 4],
            ['category' => 'Llantas y Rines', 'name' => 'Kit válvulas TPMS',          'description' => 'Sensores de presión de llantas inalámbricos.',                 'price' => 1200.00,  'sku' => 'LLR-004', 'stock' => 15],
        ];

        foreach ($simpleProducts as $data) {
            $category = BoutiqueCategory::where('name', $data['category'])->first();
            if (!$category) {
                $this->command->warn("Categoría '{$data['category']}' no encontrada, omitiendo {$data['name']}");
                continue;
            }
            BoutiqueProduct::firstOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id' => $category->id,
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'price'       => $data['price'],
                    'stock'       => $data['stock'],
                    'active'      => true,
                ]
            );
        }

        // ── Variable products (with color + size variants) ────────────────────
        $variableProducts = [
            [
                'category'    => 'Life Style',
                'name'        => 'Playera BMW Motorrad',
                'description' => 'Playera 100% algodón con logo bordado. Disponible en varios colores y tallas.',
                'price'       => 650.00,
                'sku'         => 'LST-001',
                'stock'       => 0, // stock managed by variants
                'variants'    => [
                    // Negro
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'S',   'sku' => 'LST-001-NEG-S',  'stock' => 5],
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'M',   'sku' => 'LST-001-NEG-M',  'stock' => 8],
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'L',   'sku' => 'LST-001-NEG-L',  'stock' => 6],
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'XL',  'sku' => 'LST-001-NEG-XL', 'stock' => 4],
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'XXL', 'sku' => 'LST-001-NEG-XXL','stock' => 2],
                    // Blanco
                    ['color' => 'Blanco', 'color_hex' => '#f5f5f5', 'size' => 'S',   'sku' => 'LST-001-BLA-S',  'stock' => 4],
                    ['color' => 'Blanco', 'color_hex' => '#f5f5f5', 'size' => 'M',   'sku' => 'LST-001-BLA-M',  'stock' => 7],
                    ['color' => 'Blanco', 'color_hex' => '#f5f5f5', 'size' => 'L',   'sku' => 'LST-001-BLA-L',  'stock' => 5],
                    ['color' => 'Blanco', 'color_hex' => '#f5f5f5', 'size' => 'XL',  'sku' => 'LST-001-BLA-XL', 'stock' => 0], // agotado
                    ['color' => 'Blanco', 'color_hex' => '#f5f5f5', 'size' => 'XXL', 'sku' => 'LST-001-BLA-XXL','stock' => 3],
                    // Azul BMW
                    ['color' => 'Azul',   'color_hex' => '#1c69d4', 'size' => 'S',   'sku' => 'LST-001-AZU-S',  'stock' => 3],
                    ['color' => 'Azul',   'color_hex' => '#1c69d4', 'size' => 'M',   'sku' => 'LST-001-AZU-M',  'stock' => 6],
                    ['color' => 'Azul',   'color_hex' => '#1c69d4', 'size' => 'L',   'sku' => 'LST-001-AZU-L',  'stock' => 4],
                    ['color' => 'Azul',   'color_hex' => '#1c69d4', 'size' => 'XL',  'sku' => 'LST-001-AZU-XL', 'stock' => 2],
                    ['color' => 'Azul',   'color_hex' => '#1c69d4', 'size' => 'XXL', 'sku' => 'LST-001-AZU-XXL','stock' => 0], // agotado
                ],
            ],
            [
                'category'    => 'Life Style',
                'name'        => 'Sudadera MINI Cooper',
                'description' => 'Sudadera con capucha y logo MINI bordado. Tejido fleece suave, disponible en varios colores.',
                'price'       => 1100.00,
                'sku'         => 'LST-003',
                'stock'       => 0,
                'variants'    => [
                    // Gris
                    ['color' => 'Gris',   'color_hex' => '#9ca3af', 'size' => 'S',  'sku' => 'LST-003-GRI-S',  'stock' => 4],
                    ['color' => 'Gris',   'color_hex' => '#9ca3af', 'size' => 'M',  'sku' => 'LST-003-GRI-M',  'stock' => 6],
                    ['color' => 'Gris',   'color_hex' => '#9ca3af', 'size' => 'L',  'sku' => 'LST-003-GRI-L',  'stock' => 5],
                    ['color' => 'Gris',   'color_hex' => '#9ca3af', 'size' => 'XL', 'sku' => 'LST-003-GRI-XL', 'stock' => 3],
                    // Negro
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'S',  'sku' => 'LST-003-NEG-S',  'stock' => 5],
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'M',  'sku' => 'LST-003-NEG-M',  'stock' => 8],
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'L',  'sku' => 'LST-003-NEG-L',  'stock' => 0], // agotado
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'XL', 'sku' => 'LST-003-NEG-XL', 'stock' => 2],
                    // Rojo MINI
                    ['color' => 'Rojo',   'color_hex' => '#dc2626', 'size' => 'S',  'sku' => 'LST-003-ROJ-S',  'stock' => 2],
                    ['color' => 'Rojo',   'color_hex' => '#dc2626', 'size' => 'M',  'sku' => 'LST-003-ROJ-M',  'stock' => 4],
                    ['color' => 'Rojo',   'color_hex' => '#dc2626', 'size' => 'L',  'sku' => 'LST-003-ROJ-L',  'stock' => 3],
                    ['color' => 'Rojo',   'color_hex' => '#dc2626', 'size' => 'XL', 'sku' => 'LST-003-ROJ-XL', 'stock' => 1],
                ],
            ],
            [
                'category'    => 'Rider G&G',
                'name'        => 'Guantes BMW ProSummer',
                'description' => 'Guantes de verano con protecciones certificadas CE. Palma de cuero y dorso textil transpirable.',
                'price'       => 2200.00,
                'sku'         => 'RGG-002',
                'stock'       => 0,
                'variants'    => [
                    // Solo tallas, sin color (color_hex null)
                    ['color' => 'Negro', 'color_hex' => '#1a1a1a', 'size' => 'S',   'sku' => 'RGG-002-S',  'stock' => 4],
                    ['color' => 'Negro', 'color_hex' => '#1a1a1a', 'size' => 'M',   'sku' => 'RGG-002-M',  'stock' => 6],
                    ['color' => 'Negro', 'color_hex' => '#1a1a1a', 'size' => 'L',   'sku' => 'RGG-002-L',  'stock' => 5],
                    ['color' => 'Negro', 'color_hex' => '#1a1a1a', 'size' => 'XL',  'sku' => 'RGG-002-XL', 'stock' => 3],
                    ['color' => 'Negro', 'color_hex' => '#1a1a1a', 'size' => 'XXL', 'sku' => 'RGG-002-XXL','stock' => 0],
                    ['color' => 'Blanco','color_hex' => '#f5f5f5', 'size' => 'S',   'sku' => 'RGG-002-W-S',  'stock' => 2],
                    ['color' => 'Blanco','color_hex' => '#f5f5f5', 'size' => 'M',   'sku' => 'RGG-002-W-M',  'stock' => 4],
                    ['color' => 'Blanco','color_hex' => '#f5f5f5', 'size' => 'L',   'sku' => 'RGG-002-W-L',  'stock' => 3],
                    ['color' => 'Blanco','color_hex' => '#f5f5f5', 'size' => 'XL',  'sku' => 'RGG-002-W-XL', 'stock' => 1],
                    ['color' => 'Blanco','color_hex' => '#f5f5f5', 'size' => 'XXL', 'sku' => 'RGG-002-W-XXL','stock' => 0],
                ],
            ],
            [
                'category'    => 'Rider G&G',
                'name'        => 'Chaqueta BMW AirShell',
                'description' => 'Chaqueta textil con protecciones CE nivel 2 en hombros y codos, membrana impermeable extraíble.',
                'price'       => 9800.00,
                'sku'         => 'RGG-003',
                'stock'       => 0,
                'variants'    => [
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'S',   'sku' => 'RGG-003-NEG-S',  'stock' => 2],
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'M',   'sku' => 'RGG-003-NEG-M',  'stock' => 3],
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'L',   'sku' => 'RGG-003-NEG-L',  'stock' => 2],
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'XL',  'sku' => 'RGG-003-NEG-XL', 'stock' => 1],
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => 'XXL', 'sku' => 'RGG-003-NEG-XXL','stock' => 0],
                    ['color' => 'Gris',   'color_hex' => '#6b7280', 'size' => 'S',   'sku' => 'RGG-003-GRI-S',  'stock' => 1],
                    ['color' => 'Gris',   'color_hex' => '#6b7280', 'size' => 'M',   'sku' => 'RGG-003-GRI-M',  'stock' => 2],
                    ['color' => 'Gris',   'color_hex' => '#6b7280', 'size' => 'L',   'sku' => 'RGG-003-GRI-L',  'stock' => 2],
                    ['color' => 'Gris',   'color_hex' => '#6b7280', 'size' => 'XL',  'sku' => 'RGG-003-GRI-XL', 'stock' => 1],
                    ['color' => 'Gris',   'color_hex' => '#6b7280', 'size' => 'XXL', 'sku' => 'RGG-003-GRI-XXL','stock' => 0],
                ],
            ],
            [
                'category'    => 'Life Style',
                'name'        => 'Gorra BMW M',
                'description' => 'Gorra snapback con logo BMW M bordado. Talla única ajustable, disponible en varios colores.',
                'price'       => 420.00,
                'sku'         => 'LST-002',
                'stock'       => 0,
                'variants'    => [
                    // Solo colores, sin talla
                    ['color' => 'Negro',  'color_hex' => '#1a1a1a', 'size' => null, 'sku' => 'LST-002-NEG', 'stock' => 15],
                    ['color' => 'Blanco', 'color_hex' => '#f5f5f5', 'size' => null, 'sku' => 'LST-002-BLA', 'stock' => 12],
                    ['color' => 'Azul',   'color_hex' => '#1c69d4', 'size' => null, 'sku' => 'LST-002-AZU', 'stock' => 10],
                    ['color' => 'Rojo',   'color_hex' => '#dc2626', 'size' => null, 'sku' => 'LST-002-ROJ', 'stock' => 0],
                ],
            ],
        ];

        // Simple products without variants
        $simpleOnly = [
            ['category' => 'Life Style',  'name' => 'Mochila BMW',              'description' => 'Mochila urbana 25L con compartimento para laptop.',                  'price' => 1850.00,  'sku' => 'LST-004', 'stock' => 12],
            ['category' => 'Rider G&G',   'name' => 'Casco BMW System 7 Carbon','description' => 'Casco modular de fibra de carbono con Bluetooth integrado.',         'price' => 18500.00, 'sku' => 'RGG-001', 'stock' => 5],
            ['category' => 'Rider G&G',   'name' => 'Botas BMW Allround',        'description' => 'Botas de moto certificadas CE con suela antideslizante.',            'price' => 5400.00,  'sku' => 'RGG-004', 'stock' => 10],
        ];

        foreach ($simpleOnly as $data) {
            $category = BoutiqueCategory::where('name', $data['category'])->first();
            if (!$category) continue;
            BoutiqueProduct::firstOrCreate(
                ['sku' => $data['sku']],
                ['category_id' => $category->id, 'name' => $data['name'], 'description' => $data['description'], 'price' => $data['price'], 'stock' => $data['stock'], 'active' => true]
            );
        }

        // Variable products with variants
        $variantCount = 0;
        foreach ($variableProducts as $data) {
            $category = BoutiqueCategory::where('name', $data['category'])->first();
            if (!$category) {
                $this->command->warn("Categoría '{$data['category']}' no encontrada, omitiendo {$data['name']}");
                continue;
            }

            $product = BoutiqueProduct::firstOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id' => $category->id,
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'price'       => $data['price'],
                    'stock'       => $data['stock'],
                    'active'      => true,
                ]
            );

            foreach ($data['variants'] as $v) {
                BoutiqueProductVariant::firstOrCreate(
                    ['sku' => $v['sku']],
                    [
                        'product_id' => $product->id,
                        'color'      => $v['color'],
                        'color_hex'  => $v['color_hex'],
                        'size'       => $v['size'],
                        'stock'      => $v['stock'],
                        'active'     => true,
                    ]
                );
                $variantCount++;
            }
        }

        $this->command->info('Productos simples creados/actualizados.');
        $this->command->info("Productos variables creados con {$variantCount} variantes.");
    }
}
