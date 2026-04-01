<?php

namespace App\Services\Boutique;

use App\Models\Boutique\BoutiqueInventoryMovement;
use App\Models\Boutique\BoutiqueProduct;
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
