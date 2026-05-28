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

    /** @var list<string> */
    private const SALES_ROLES = [
        'seller',
        'marketing',
        'receptionist',
        'gestor',
        'appointment_manager',
    ];

    public function __construct(
        private readonly AssistantAdvisorAvailabilityService $availability
    ) {}

    public function assignSalesUserIdForDealership(int $dealershipId): ?int
    {
        if ($this->availability->tablesReady()) {
            $available = $this->availability->pickAvailableUserIdForDealership($dealershipId, true);

            return $available;
        }

        return $this->legacyAssignForRoles($dealershipId, self::SALES_ROLES)
            ?? $this->legacyAssignForRoles($dealershipId, self::STAFF_ROLES);
    }

    public function assignUserIdForDealership(int $dealershipId): ?int
    {
        if ($this->availability->tablesReady()) {
            return $this->availability->pickAvailableUserIdForDealership($dealershipId, false);
        }

        return $this->legacyAssignForRoles($dealershipId, self::STAFF_ROLES);
    }

    /**
     * @param  list<string>  $roles
     */
    private function legacyAssignForRoles(int $dealershipId, array $roles): ?int
    {
        $dealershipTable = (new Dealership)->getTable();

        foreach ($roles as $role) {
            $userId = User::query()
                ->whereHas('roles', fn ($q) => $q->where('name', $role))
                ->whereHas('dealerships', fn ($q) => $q->where($dealershipTable.'.id', $dealershipId))
                ->orderBy('id')
                ->value('id');

            if ($userId) {
                return (int) $userId;
            }
        }

        if ($roles !== self::STAFF_ROLES) {
            return null;
        }

        $fallback = User::query()
            ->whereHas('dealerships', fn ($q) => $q->where($dealershipTable.'.id', $dealershipId))
            ->orderBy('id')
            ->value('id');

        return $fallback ? (int) $fallback : null;
    }
}
