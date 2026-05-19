<?php

namespace App\Services\Boutique;

use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductVariant;
use Exception;
use Illuminate\Support\Collection;

/**
 * Resuelve líneas de checkout (producto + variante opcional), precio y stock.
 */
class BoutiqueCheckoutLineService
{
    public function __construct(
        protected BoutiqueInventoryService $inventoryService,
    ) {}

    /**
     * @param  array<int, array{product_uuid: string, quantity: int, variant_uuid?: string|null}>  $items
     * @return array{lines: array<int, array{product: BoutiqueProduct, variant: ?BoutiqueProductVariant, quantity: int, unit_price: float, subtotal: float, product_name: string, product_sku: string}>, insufficient: array<int, array{product: string, available: int, requested: int}>}
     */
    public function resolveLines(array $items): array
    {
        $productUuids = collect($items)->pluck('product_uuid')->unique()->values()->all();
        $products = BoutiqueProduct::whereIn('uuid', $productUuids)->get()->keyBy('uuid');

        $variantUuids = collect($items)
            ->pluck('variant_uuid')
            ->filter(fn ($v) => is_string($v) && $v !== '')
            ->unique()
            ->values()
            ->all();

        $variants = $variantUuids !== []
            ? BoutiqueProductVariant::whereIn('uuid', $variantUuids)->where('active', true)->get()->keyBy('uuid')
            : collect();

        $lines = [];
        $insufficient = [];

        foreach ($items as $item) {
            $product = $products[$item['product_uuid']] ?? null;
            if (! $product) {
                throw new Exception('PRODUCT_NOT_FOUND:' . $item['product_uuid']);
            }

            $variant = null;
            $variantUuid = $item['variant_uuid'] ?? null;
            if (is_string($variantUuid) && $variantUuid !== '') {
                $variant = $variants[$variantUuid] ?? null;
                if (! $variant || (int) $variant->product_id !== (int) $product->id) {
                    throw new Exception('PRODUCT_VARIANT_NOT_FOUND');
                }
            }

            $qty = (int) $item['quantity'];
            $available = $variant ? (int) $variant->stock : (int) $product->stock;
            if ($available < $qty) {
                $label = $product->name;
                if ($variant) {
                    $label .= ' (' . trim(($variant->color ?? '') . ' ' . ($variant->size ?? '')) . ')';
                }
                $insufficient[] = [
                    'product' => $label,
                    'available' => $available,
                    'requested' => $qty,
                ];
                continue;
            }

            $unitPrice = $variant
                ? (float) ($variant->price ?? $product->price)
                : (float) $product->price;

            $name = $product->name;
            if ($variant) {
                $parts = array_filter([$variant->color, $variant->size]);
                if ($parts !== []) {
                    $name .= ' — ' . implode(' / ', $parts);
                }
            }

            $sku = $variant && $variant->sku ? $variant->sku : ($product->sku ?? '');

            $lines[] = [
                'product' => $product,
                'variant' => $variant,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => round($qty * $unitPrice, 2),
                'product_name' => $name,
                'product_sku' => $sku,
            ];
        }

        return ['lines' => $lines, 'insufficient' => $insufficient];
    }

    public function reduceLineStock(array $line, string $orderUuid): void
    {
        /** @var BoutiqueProduct $product */
        $product = $line['product'];
        /** @var ?BoutiqueProductVariant $variant */
        $variant = $line['variant'];
        $qty = (int) $line['quantity'];

        if ($variant) {
            $this->inventoryService->reduceVariantStock($variant, $qty, 'venta', $orderUuid);
        } else {
            $this->inventoryService->reduceStock($product, $qty, 'venta', $orderUuid);
        }
    }

    public function restoreLineStock(array $line, string $orderUuid): void
    {
        $product = $line['product'];
        $variant = $line['variant'];
        $qty = (int) $line['quantity'];

        if ($variant) {
            $this->inventoryService->restoreVariantStock($variant, $qty, 'cancelacion', $orderUuid);
        } else {
            $this->inventoryService->restoreStock($product, $qty, 'cancelacion', $orderUuid);
        }
    }
}
