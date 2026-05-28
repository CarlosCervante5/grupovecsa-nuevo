<?php

namespace App\Services\Boutique;

use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductImage;
use Illuminate\Database\Eloquent\Builder;

/**
 * Regla de negocio: inventario público boutique = activo + imagen subida + stock disponible.
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

    public static function hasAvailableStock(BoutiqueProduct $product): bool
    {
        if ((int) $product->stock > 0) {
            return true;
        }

        return $product->allVariants()
            ->where('active', true)
            ->where('stock', '>', 0)
            ->exists();
    }

    public static function isPublished(BoutiqueProduct $product): bool
    {
        return (bool) $product->active
            && self::hasPublishableImage($product)
            && self::hasAvailableStock($product);
    }

    /**
     * Catálogo público y asistente: activo + imagen uploaded + stock (producto o variante activa).
     */
    public static function applyPublishedScope(Builder $query): Builder
    {
        return $query->where('active', true)
            ->whereHas('images', function (Builder $q) {
                $q->where('status', 'uploaded')
                    ->where('image_path', '!=', '');
            })
            ->where(function (Builder $q) {
                $q->where('stock', '>', 0)
                    ->orWhereHas('allVariants', function (Builder $v) {
                        $v->where('active', true)->where('stock', '>', 0);
                    });
            });
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
        return $wantsPublished && self::hasPublishableImage($product);
    }
}
