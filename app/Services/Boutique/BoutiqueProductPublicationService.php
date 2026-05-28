<?php

namespace App\Services\Boutique;

use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductImage;
use Illuminate\Database\Eloquent\Builder;

/**
 * Regla de negocio: inventario público boutique = activo + imagen + stock vendible + precio > 0.
 */
class BoutiqueProductPublicationService
{
    public static function hasPublishableImage(BoutiqueProduct|int $product): bool
    {
        $productId = $product instanceof BoutiqueProduct ? (int) $product->id : (int) $product;

        if ($productId <= 0) {
            return false;
        }

        return BoutiqueProductImage::query()
            ->where('product_id', $productId)
            ->where('status', 'uploaded')
            ->where('image_path', '!=', '')
            ->exists();
    }

    /**
     * Stock mostrable en listados: suma variantes activas o stock del producto simple.
     */
    public static function catalogDisplayStock(BoutiqueProduct $product): int
    {
        $variantStock = (int) $product->allVariants()
            ->where('active', true)
            ->sum('stock');

        if ($variantStock > 0) {
            return $variantStock;
        }

        return max(0, (int) $product->stock);
    }

    public static function hasAvailableStock(BoutiqueProduct $product): bool
    {
        return self::catalogDisplayStock($product) > 0;
    }

    public static function hasValidPrice(BoutiqueProduct $product): bool
    {
        if ((float) $product->price > 0) {
            return true;
        }

        return $product->allVariants()
            ->where('active', true)
            ->where('stock', '>', 0)
            ->where('price', '>', 0)
            ->exists();
    }

    public static function isPublished(BoutiqueProduct $product): bool
    {
        return (bool) $product->active
            && self::hasPublishableImage($product)
            && self::hasAvailableStock($product)
            && self::hasValidPrice($product);
    }

    /**
     * Catálogo público y asistente: activo + imagen + stock vendible + precio válido.
     */
    public static function applyPublishedScope(Builder $query): Builder
    {
        return $query->where('active', true)
            ->whereHas('images', function (Builder $q) {
                $q->where('status', 'uploaded')
                    ->where('image_path', '!=', '');
            })
            ->where(function (Builder $q) {
                $q->where(function (Builder $stock) {
                    $stock->where('stock', '>', 0)
                        ->orWhereHas('allVariants', function (Builder $v) {
                            $v->where('active', true)->where('stock', '>', 0);
                        });
                })->where(function (Builder $price) {
                    $price->where('price', '>', 0)
                        ->orWhereHas('allVariants', function (Builder $v) {
                            $v->where('active', true)
                                ->where('stock', '>', 0)
                                ->where('price', '>', 0);
                        });
                });
            });
    }

    /**
     * Metadatos para tarjetas del catálogo (stock/precio efectivos).
     *
     * @return array{catalog_stock: int, in_stock: bool, catalog_price: float}
     */
    public static function catalogPresentation(BoutiqueProduct $product): array
    {
        $catalogStock = self::catalogDisplayStock($product);
        $catalogPrice = (float) $product->price;

        if ($catalogPrice <= 0) {
            $variantPrice = $product->allVariants()
                ->where('active', true)
                ->where('stock', '>', 0)
                ->where('price', '>', 0)
                ->min('price');

            $catalogPrice = $variantPrice !== null ? (float) $variantPrice : 0.0;
        }

        return [
            'catalog_stock' => $catalogStock,
            'in_stock' => $catalogStock > 0,
            'catalog_price' => $catalogPrice,
        ];
    }

    /**
     * @return string|null Mensaje de error si no puede activarse
     */
    public static function validateActivation(BoutiqueProduct $product, bool $requestedActive): ?string
    {
        if ($requestedActive && ! self::hasPublishableImage($product)) {
            return 'El producto no puede publicarse sin al menos una imagen subida correctamente.';
        }

        return null;
    }

    public static function syncActiveAfterImageChange(BoutiqueProduct $product): void
    {
        $product->refresh();

        if ($product->active && ! self::hasPublishableImage($product)) {
            $product->update(['active' => false]);
        }
    }

    /**
     * Tras importar imágenes WC: respeta "Publicado" solo si hay imagen válida.
     */
    public static function resolveActiveFromImportFlag(BoutiqueProduct $product, bool $wantsPublished): bool
    {
        return $wantsPublished
            && self::hasPublishableImage($product)
            && self::hasAvailableStock($product)
            && self::hasValidPrice($product);
    }
}
