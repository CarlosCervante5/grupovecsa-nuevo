<?php

namespace App\Services\Assistant;

use App\Models\Dealership;
use App\Models\User;

class AssistantDealershipAssigner
{
    /** @var list<string> */
    private const STAFF_ROLES = [
        'marketing',
        'seller',
        'receptionist',
        'gestor',
        'appointment_manager',
    ];

    public function assignUserIdForDealership(int $dealershipId): ?int
    {
        $dealershipTable = (new Dealership)->getTable();

        foreach (self::STAFF_ROLES as $role) {
            $userId = User::query()
                ->whereHas('roles', fn ($q) => $q->where('name', $role))
                ->whereHas('dealerships', fn ($q) => $q->where($dealershipTable.'.id', $dealershipId))
                ->orderBy('id')
                ->value('id');

            if ($userId) {
                return (int) $userId;
            }
        }

        $fallback = User::query()
            ->whereHas('dealerships', fn ($q) => $q->where($dealershipTable.'.id', $dealershipId))
            ->orderBy('id')
            ->value('id');

        return $fallback ? (int) $fallback : null;
    }
}
