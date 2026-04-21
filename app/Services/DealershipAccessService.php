<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DealershipAccessService
{
    /**
     * Roles que ven inventario de todas las sucursales (sin filtro por pivote).
     */
    public const INVENTORY_BYPASS_ROLES = [
        'administrator',
        'developer',
        'gerente',
        'admin',
    ];

    /**
     * IDs de sucursales a las que el usuario está limitado en inventario admin.
     * null = sin restricción (invitado, bypass, o usuario sin sucursales asignadas).
     *
     * @return list<int>|null
     */
    public function inventoryDealershipIds(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        foreach (self::INVENTORY_BYPASS_ROLES as $role) {
            if ($user->hasRole($role)) {
                return null;
            }
        }

        $ids = $user->dealerships()->pluck('id')->all();
        if ($ids === []) {
            return null;
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * @param  list<int>|null  $scopeIds  resultado de inventoryDealershipIds
     */
    public function assertDealershipAllowed(?User $user, ?int $dealershipId, ?array $scopeIds = null): void
    {
        $scopeIds ??= $this->inventoryDealershipIds($user);
        if ($scopeIds === null || $dealershipId === null) {
            return;
        }
        if (! in_array((int) $dealershipId, $scopeIds, true)) {
            throw new AuthorizationException('No tienes permiso para operar con inventario de esta sucursal.');
        }
    }

    /**
     * Producto boutique sin sucursal: solo roles bypass; usuarios con sucursal asignada no pueden gestionarlo.
     */
    public function assertProductDealershipAccessible(?User $user, ?int $productDealershipId, ?array $scopeIds = null): void
    {
        $scopeIds ??= $this->inventoryDealershipIds($user);
        if ($scopeIds === null) {
            return;
        }
        if ($productDealershipId === null) {
            throw new AuthorizationException('Este producto no está asignado a una sucursal.');
        }
        $this->assertDealershipAllowed($user, $productDealershipId, $scopeIds);
    }

    /**
     * ID de sucursal para un producto boutique nuevo (admin).
     *
     * @throws ValidationException
     */
    public function resolveDealershipIdForNewBoutiqueProduct(Request $request): ?int
    {
        $user = $request->user();
        $scopeIds = $this->inventoryDealershipIds($user);
        if ($scopeIds !== null) {
            if (count($scopeIds) === 1) {
                return $scopeIds[0];
            }
            $requested = (int) $request->input('dealership_id', 0);
            if (! $requested || ! in_array($requested, $scopeIds, true)) {
                throw ValidationException::withMessages([
                    'dealership_id' => ['Selecciona una sucursal válida (dealership_id).'],
                ]);
            }

            return $requested;
        }
        if ($request->filled('dealership_id')) {
            return (int) $request->input('dealership_id');
        }

        return null;
    }
}
