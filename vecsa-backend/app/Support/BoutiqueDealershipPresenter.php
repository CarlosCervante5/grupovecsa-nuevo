<?php

namespace App\Support;

use App\Models\Dealership;

/** Resúmenes de sucursal para catálogo, carrito y checkout boutique. */
final class BoutiqueDealershipPresenter
{
    /**
     * Catálogo / detalle de producto (sin WhatsApp).
     *
     * @return array<string, mixed>|null
     */
    public static function catalogSummary(?Dealership $dealership): ?array
    {
        if ($dealership === null) {
            return null;
        }

        return [
            'id' => $dealership->id,
            'name' => $dealership->name,
            'location' => $dealership->location,
            'state' => $dealership->state,
        ];
    }

    /**
     * Checkout: incluye teléfono WhatsApp para ventas.
     *
     * @return array<string, mixed>|null
     */
    public static function checkoutSummary(?Dealership $dealership): ?array
    {
        if ($dealership === null) {
            return null;
        }

        return [
            'id' => $dealership->id,
            'name' => $dealership->name,
            'location' => $dealership->location,
            'state' => $dealership->state,
            'whatsapp_phone' => trim((string) ($dealership->whatsapp_phone ?? '')),
        ];
    }
}
