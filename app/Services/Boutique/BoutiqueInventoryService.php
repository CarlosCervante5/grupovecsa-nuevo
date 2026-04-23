<?php

namespace App\Services\Boutique;

use App\Models\Boutique\BoutiqueInventoryMovement;
use App\Models\Boutique\BoutiqueOrder;
use App\Models\Boutique\BoutiqueOrderItem;
use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductVariant;
use Exception;

class BoutiqueInventoryService
{
    /**
     * Reduce stock for a product and create an inventory movement record.
     *
     * @throws Exception if insufficient stock
     */
    public function reduceStock(BoutiqueProduct $product, int $quantity, string $reason, ?string $referenceUuid = null): BoutiqueInventoryMovement
    {
        if ($product->stock < $quantity) {
            throw new Exception("Stock insuficiente para el producto {$product->name}. Disponible: {$product->stock}, solicitado: {$quantity}");
        }

        $previousStock = $product->stock;
        $newStock = $previousStock - $quantity;

        $product->update(['stock' => $newStock]);

        return BoutiqueInventoryMovement::create([
            'product_id' => $product->id,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'quantity_change' => -$quantity,
            'reason' => $reason,
            'reference_type' => $referenceUuid ? 'order' : null,
            'reference_uuid' => $referenceUuid,
        ]);
    }

    /**
     * Reduce stock on a product variant. Movements se registran bajo el producto padre (columna product_id).
     */
    public function reduceVariantStock(
        BoutiqueProductVariant $variant,
        int $quantity,
        string $reason,
        ?string $referenceUuid = null
    ): BoutiqueInventoryMovement {
        $variant->refresh();
        if ($variant->stock < $quantity) {
            throw new Exception(
                "Stock insuficiente para la variante ({$variant->sku}). Disponible: {$variant->stock}, solicitado: {$quantity}"
            );
        }
        $previousStock = $variant->stock;
        $newStock = $previousStock - $quantity;
        $variant->update(['stock' => $newStock]);

        return BoutiqueInventoryMovement::create([
            'product_id' => $variant->product_id,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'quantity_change' => -$quantity,
            'reason' => $reason . " (variante_id={$variant->id})",
            'reference_type' => $referenceUuid ? 'order' : null,
            'reference_uuid' => $referenceUuid,
        ]);
    }

    /**
     * Restore stock on a product variant.
     */
    public function restoreVariantStock(
        BoutiqueProductVariant $variant,
        int $quantity,
        string $reason,
        ?string $referenceUuid = null
    ): BoutiqueInventoryMovement {
        $variant->refresh();
        $previousStock = $variant->stock;
        $newStock = $previousStock + $quantity;
        $variant->update(['stock' => $newStock]);

        return BoutiqueInventoryMovement::create([
            'product_id' => $variant->product_id,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'quantity_change' => $quantity,
            'reason' => $reason . " (variante_id={$variant->id})",
            'reference_type' => $referenceUuid ? 'order' : null,
            'reference_uuid' => $referenceUuid,
        ]);
    }

    public function orderLacksStockForItems(BoutiqueOrder $order): bool
    {
        $order->loadMissing('orderItems.product');
        foreach ($order->orderItems as $line) {
            if ($line->product_variant_id) {
                $v = BoutiqueProductVariant::query()->find($line->product_variant_id);
                if (! $v || (int) $v->stock < (int) $line->quantity) {
                    return true;
                }
            } else {
                $p = $line->product;
                if (! $p || (int) $p->stock < (int) $line->quantity) {
                    return true;
                }
            }
        }

        return false;
    }

    public function applySaleForEntireOrder(BoutiqueOrder $order, string $orderUuid, string $reason = 'venta'): void
    {
        $order->load('orderItems.product', 'orderItems');
        foreach ($order->orderItems as $line) {
            $this->applySaleForOrderItem($line, $reason, $orderUuid);
        }
    }

    public function applySaleForOrderItem(BoutiqueOrderItem $line, string $reason, string $orderUuid): void
    {
        if ($line->product_variant_id) {
            $v = BoutiqueProductVariant::query()->lockForUpdate()->find($line->product_variant_id);
            if (! $v) {
                throw new Exception('Línea de pedido: variante no encontrada (id ' . (string) $line->product_variant_id . ').');
            }
            $this->reduceVariantStock($v, (int) $line->quantity, $reason, $orderUuid);
            return;
        }
        if ($line->product) {
            $p = BoutiqueProduct::query()->lockForUpdate()->find($line->product_id);
            if (! $p) {
                throw new Exception('Línea de pedido: producto no encontrado.');
            }
            $this->reduceStock($p, (int) $line->quantity, $reason, $orderUuid);
        }
    }

    public function restoreSaleForOrderItem(BoutiqueOrderItem $line, string $reason, string $orderUuid): void
    {
        if ($line->product_variant_id) {
            $v = BoutiqueProductVariant::find($line->product_variant_id);
            if ($v) {
                $this->restoreVariantStock($v, (int) $line->quantity, $reason, $orderUuid);
            }
            return;
        }
        if ($line->product) {
            $this->restoreStock(
                $line->product,
                (int) $line->quantity,
                $reason,
                $orderUuid
            );
        }
    }

    /**
     * Restore stock for a product and create an inventory movement record.
     */
    public function restoreStock(BoutiqueProduct $product, int $quantity, string $reason, ?string $referenceUuid = null): BoutiqueInventoryMovement
    {
        $previousStock = $product->stock;
        $newStock = $previousStock + $quantity;

        $product->update(['stock' => $newStock]);

        return BoutiqueInventoryMovement::create([
            'product_id' => $product->id,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'quantity_change' => $quantity,
            'reason' => $reason,
            'reference_type' => $referenceUuid ? 'order' : null,
            'reference_uuid' => $referenceUuid,
        ]);
    }

    /**
     * Manually adjust stock to a new value and create an inventory movement record.
     */
    public function manualAdjust(BoutiqueProduct $product, int $newStock, string $reason): BoutiqueInventoryMovement
    {
        $previousStock = $product->stock;
        $quantityChange = $newStock - $previousStock;

        $product->update(['stock' => $newStock]);

        return BoutiqueInventoryMovement::create([
            'product_id' => $product->id,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'quantity_change' => $quantityChange,
            'reason' => $reason,
            'reference_type' => 'manual',
            'reference_uuid' => null,
        ]);
    }
}
