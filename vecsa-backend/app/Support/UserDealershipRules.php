<?php

namespace App\Support;

/**
 * Roles operativos que pueden tener varias sucursales asignadas (pivote dealership_user).
 */
class UserDealershipRules
{
    public const MULTI_DEALERSHIP_ROLES = [
        'marketing',
        'gestor',
        'manager',
        'staff',
        'receptionist',
        'valuator',
        'appointment_manager',
        'seller',
        'spare_parts',
        'bodywork_paint_technician',
        'technician',
        'gerente',
        'strega-seller',
        'strega-manager',
        'strega-administrator',
    ];

    public static function allowsMultipleDealerships(?string $roleName): bool
    {
        if ($roleName === null || trim($roleName) === '') {
            return false;
        }

        return in_array(strtolower(trim($roleName)), self::MULTI_DEALERSHIP_ROLES, true);
    }
}
