<?php

namespace App\Services\Boutique;

use App\Models\Boutique\BoutiqueProductAttribute;
use App\Models\Boutique\BoutiqueProductAttributeValue;
use App\Models\Boutique\BoutiqueProductVariant;
use App\Models\Boutique\BoutiqueVariantAttributeValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BoutiqueVariantAttributeCatalogSync
{
    /**
     * A partir de columnas color/size en variantes, crea atributos globales Color y Talla,
     * valores, pivote producto↔atributo y variante↔valor (lo que espera el admin de tienda).
     *
     * @param  list<int>|null  $productIds  Solo variantes de estos productos padre; null = todas las variantes activas.
     * @return array{products_touched: int, variant_links_added: int, attribute_values_created: int}
     */
    public function sync(?array $productIds = null): array
    {
        $agg = ['products_touched' => 0, 'variant_links_added' => 0, 'attribute_values_created' => 0];

        if ($productIds !== null) {
            $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
            if ($productIds === []) {
                return $agg;
            }
            $variants = BoutiqueProductVariant::query()
                ->where('active', true)
                ->whereIn('product_id', $productIds)
                ->get();

            return $this->syncVariantCollection($variants);
        }

        BoutiqueProductVariant::query()
            ->where('active', true)
            ->orderBy('id')
            ->chunk(400, function (Collection $chunk) use (&$agg) {
                $partial = $this->syncVariantCollection($chunk);
                $agg['variant_links_added'] += $partial['variant_links_added'];
                $agg['attribute_values_created'] += $partial['attribute_values_created'];
            });

        return $agg;
    }

    /**
     * @param  Collection<int, BoutiqueProductVariant>  $variants
     * @return array{products_touched: int, variant_links_added: int, attribute_values_created: int}
     */
    private function syncVariantCollection(Collection $variants): array
    {
        $out = ['products_touched' => 0, 'variant_links_added' => 0, 'attribute_values_created' => 0];
        if ($variants->isEmpty()) {
            return $out;
        }

        $prefix = env('DB_TABLE_PREFIX', '');
        $pivotProduct = $prefix . 'boutique_product_attribute_product';
        $pivotVariant = $prefix . 'boutique_variant_attribute_values';

        $colorAttr = BoutiqueProductAttribute::firstOrCreate(['name' => 'Color']);
        $sizeAttr = BoutiqueProductAttribute::firstOrCreate(['name' => 'Talla']);

        /** @var array<string, BoutiqueProductAttributeValue> */
        $colorValueMap = [];
        /** @var array<string, BoutiqueProductAttributeValue> */
        $sizeValueMap = [];

        foreach ($variants as $v) {
            if ($v->color !== null && $v->color !== '') {
                $k = (string) $v->color;
                if (! isset($colorValueMap[$k])) {
                    $before = BoutiqueProductAttributeValue::where('attribute_id', $colorAttr->id)->where('value', $v->color)->exists();
                    $colorValueMap[$k] = BoutiqueProductAttributeValue::firstOrCreate(
                        ['attribute_id' => $colorAttr->id, 'value' => $v->color],
                        ['color_hex' => $v->color_hex, 'sort_order' => 0]
                    );
                    if (! $before) {
                        $out['attribute_values_created']++;
                    }
                }
            }
            if ($v->size !== null && $v->size !== '') {
                $k = (string) $v->size;
                if (! isset($sizeValueMap[$k])) {
                    $before = BoutiqueProductAttributeValue::where('attribute_id', $sizeAttr->id)->where('value', $v->size)->exists();
                    $sizeValueMap[$k] = BoutiqueProductAttributeValue::firstOrCreate(
                        ['attribute_id' => $sizeAttr->id, 'value' => $v->size],
                        ['sort_order' => 0]
                    );
                    if (! $before) {
                        $out['attribute_values_created']++;
                    }
                }
            }
        }

        $productsTouched = [];

        foreach ($variants as $variant) {
            $productId = $variant->product_id;

            $hasColor = $variant->color !== null && $variant->color !== '' && isset($colorValueMap[(string) $variant->color]);
            $hasSize = $variant->size !== null && $variant->size !== '' && isset($sizeValueMap[(string) $variant->size]);

            if ($hasColor) {
                if (! DB::table($pivotProduct)->where('product_id', $productId)->where('attribute_id', $colorAttr->id)->exists()) {
                    DB::table($pivotProduct)->insert([
                        'product_id' => $productId,
                        'attribute_id' => $colorAttr->id,
                    ]);
                }
                $productsTouched[$productId] = true;
                $colorValue = $colorValueMap[(string) $variant->color];
                if (! DB::table($pivotVariant)->where('variant_id', $variant->id)->where('attribute_value_id', $colorValue->id)->exists()) {
                    BoutiqueVariantAttributeValue::create([
                        'variant_id' => $variant->id,
                        'attribute_value_id' => $colorValue->id,
                    ]);
                    $out['variant_links_added']++;
                }
            }

            if ($hasSize) {
                if (! DB::table($pivotProduct)->where('product_id', $productId)->where('attribute_id', $sizeAttr->id)->exists()) {
                    DB::table($pivotProduct)->insert([
                        'product_id' => $productId,
                        'attribute_id' => $sizeAttr->id,
                    ]);
                }
                $productsTouched[$productId] = true;
                $sizeValue = $sizeValueMap[(string) $variant->size];
                if (! DB::table($pivotVariant)->where('variant_id', $variant->id)->where('attribute_value_id', $sizeValue->id)->exists()) {
                    BoutiqueVariantAttributeValue::create([
                        'variant_id' => $variant->id,
                        'attribute_value_id' => $sizeValue->id,
                    ]);
                    $out['variant_links_added']++;
                }
            }
        }

        $out['products_touched'] = count($productsTouched);

        return $out;
    }

}
