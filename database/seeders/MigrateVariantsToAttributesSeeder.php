<?php

namespace Database\Seeders;

use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductAttribute;
use App\Models\Boutique\BoutiqueProductAttributeValue;
use App\Models\Boutique\BoutiqueProductVariant;
use App\Models\Boutique\BoutiqueVariantAttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateVariantsToAttributesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting migration of legacy variants to attributes system...');

        // 1. Create "Color" and "Talla" attributes if they don't exist
        $colorAttribute = BoutiqueProductAttribute::where('name', 'Color')->first();
        if (!$colorAttribute) {
            $colorAttribute = BoutiqueProductAttribute::create(['name' => 'Color']);
            $this->command->info('Created attribute: Color');
        }

        $sizeAttribute = BoutiqueProductAttribute::where('name', 'Talla')->first();
        if (!$sizeAttribute) {
            $sizeAttribute = BoutiqueProductAttribute::create(['name' => 'Talla']);
            $this->command->info('Created attribute: Talla');
        }

        // 2. Extract unique color values from existing variants
        $prefix = env('DB_TABLE_PREFIX', '');
        $variantsTable = $prefix . 'boutique_product_variants';

        $uniqueColors = DB::table($variantsTable)
            ->whereNotNull('color')
            ->where('color', '!=', '')
            ->select('color', 'color_hex')
            ->distinct()
            ->get();

        $colorValueMap = []; // color_string => BoutiqueProductAttributeValue
        foreach ($uniqueColors as $row) {
            $existing = BoutiqueProductAttributeValue::where('attribute_id', $colorAttribute->id)
                ->where('value', $row->color)
                ->first();

            if (!$existing) {
                $existing = BoutiqueProductAttributeValue::create([
                    'attribute_id' => $colorAttribute->id,
                    'value' => $row->color,
                    'color_hex' => $row->color_hex,
                    'sort_order' => 0,
                ]);
            }
            $colorValueMap[$row->color] = $existing;
        }
        $this->command->info('Processed ' . count($colorValueMap) . ' unique color values.');

        // 3. Extract unique size values from existing variants
        $uniqueSizes = DB::table($variantsTable)
            ->whereNotNull('size')
            ->where('size', '!=', '')
            ->select('size')
            ->distinct()
            ->get();

        $sizeValueMap = []; // size_string => BoutiqueProductAttributeValue
        foreach ($uniqueSizes as $row) {
            $existing = BoutiqueProductAttributeValue::where('attribute_id', $sizeAttribute->id)
                ->where('value', $row->size)
                ->first();

            if (!$existing) {
                $existing = BoutiqueProductAttributeValue::create([
                    'attribute_id' => $sizeAttribute->id,
                    'value' => $row->size,
                    'sort_order' => 0,
                ]);
            }
            $sizeValueMap[$row->size] = $existing;
        }
        $this->command->info('Processed ' . count($sizeValueMap) . ' unique size values.');

        // 4. Create product-attribute assignments and variant-attribute-value pivots
        $pivotTable = $prefix . 'boutique_product_attribute_product';
        $variantPivotTable = $prefix . 'boutique_variant_attribute_values';

        $variants = BoutiqueProductVariant::all();
        $processedProducts = [];

        foreach ($variants as $variant) {
            $productId = $variant->product_id;
            $hasColor = !empty($variant->color) && isset($colorValueMap[$variant->color]);
            $hasSize = !empty($variant->size) && isset($sizeValueMap[$variant->size]);

            // Assign attributes to product (once per product)
            if (!isset($processedProducts[$productId])) {
                $processedProducts[$productId] = ['color' => false, 'size' => false];
            }

            if ($hasColor && !$processedProducts[$productId]['color']) {
                $exists = DB::table($pivotTable)
                    ->where('product_id', $productId)
                    ->where('attribute_id', $colorAttribute->id)
                    ->exists();

                if (!$exists) {
                    DB::table($pivotTable)->insert([
                        'product_id' => $productId,
                        'attribute_id' => $colorAttribute->id,
                    ]);
                }
                $processedProducts[$productId]['color'] = true;
            }

            if ($hasSize && !$processedProducts[$productId]['size']) {
                $exists = DB::table($pivotTable)
                    ->where('product_id', $productId)
                    ->where('attribute_id', $sizeAttribute->id)
                    ->exists();

                if (!$exists) {
                    DB::table($pivotTable)->insert([
                        'product_id' => $productId,
                        'attribute_id' => $sizeAttribute->id,
                    ]);
                }
                $processedProducts[$productId]['size'] = true;
            }

            // Create variant-attribute-value pivot records
            if ($hasColor) {
                $colorValue = $colorValueMap[$variant->color];
                $exists = DB::table($variantPivotTable)
                    ->where('variant_id', $variant->id)
                    ->where('attribute_value_id', $colorValue->id)
                    ->exists();

                if (!$exists) {
                    BoutiqueVariantAttributeValue::create([
                        'variant_id' => $variant->id,
                        'attribute_value_id' => $colorValue->id,
                    ]);
                }
            }

            if ($hasSize) {
                $sizeValue = $sizeValueMap[$variant->size];
                $exists = DB::table($variantPivotTable)
                    ->where('variant_id', $variant->id)
                    ->where('attribute_value_id', $sizeValue->id)
                    ->exists();

                if (!$exists) {
                    BoutiqueVariantAttributeValue::create([
                        'variant_id' => $variant->id,
                        'attribute_value_id' => $sizeValue->id,
                    ]);
                }
            }
        }

        $this->command->info('Processed ' . $variants->count() . ' variants across ' . count($processedProducts) . ' products.');
        $this->command->info('Migration of legacy variants to attributes system completed.');
    }
}
