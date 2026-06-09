<?php

namespace App\Support;

use App\Models\Boutique\BoutiqueProduct;

/**
 * Catálogo demo de boutique para seeders de deploy.
 * Idempotente por SKU: firstOrCreate crea solo lo que falta.
 * Si ya hay productos reales (import sandbox), no se siembran demos.
 */
final class BoutiqueDemoCatalog
{
    /** @var list<string> */
    public const PRODUCT_SKUS = [
        'ACC-001', 'ACC-002', 'ACC-003', 'ACC-004',
        'CLN-001', 'CLN-002', 'CLN-003', 'CLN-004',
        'LLR-001', 'LLR-002', 'LLR-003', 'LLR-004',
        'LST-001', 'LST-002', 'LST-003', 'LST-004',
        'RGG-001', 'RGG-002', 'RGG-003', 'RGG-004',
    ];

    /** @var list<string> */
    public const CATEGORY_NAMES = [
        'Accesorios',
        'Clean & Care',
        'Life Style',
        'Llantas y Rines',
        'Rider G&G',
    ];

    /** @var list<string> */
    public const BANNER_TITLES = [
        'Boutique BMW',
        'Accesorios Premium',
        'Estilo de Vida',
    ];

    public static function isDemoProductSku(string $sku): bool
    {
        return in_array($sku, self::PRODUCT_SKUS, true);
    }

    /**
     * Hay catálogo real importado (p. ej. sandbox → prod): no mezclar demos en deploy.
     */
    public static function hasNonDemoCatalog(): bool
    {
        return BoutiqueProduct::query()
            ->whereNotIn('sku', self::PRODUCT_SKUS)
            ->exists();
    }

    public static function shouldSeedDemoCatalog(): bool
    {
        if (filter_var(env('BOUTIQUE_SKIP_DEMO_SEEDERS', false), FILTER_VALIDATE_BOOL)) {
            return false;
        }

        return ! self::hasNonDemoCatalog();
    }
}
