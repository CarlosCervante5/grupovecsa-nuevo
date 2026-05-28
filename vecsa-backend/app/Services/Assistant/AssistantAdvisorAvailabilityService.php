<?php

namespace App\Services\Assistant;

use App\Models\AssistantAdvisorAvailability;
use App\Models\Dealership;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AssistantAdvisorAvailabilityService
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

    public function tablesReady(): bool
    {
        try {
            return Schema::hasTable((new AssistantAdvisorAvailability)->getTable());
        } catch (\Throwable) {
            return false;
        }
    }

    public function hasAvailableAdvisor(int $dealershipId): bool
    {
        if (! $this->tablesReady() || $dealershipId <= 0) {
            return true;
        }

        return $this->availableStaffQuery($dealershipId, self::STAFF_ROLES)->exists();
    }

    public function pickAvailableUserIdForDealership(int $dealershipId, bool $preferSales = false): ?int
    {
        if ($dealershipId <= 0 || ! $this->tablesReady()) {
            return null;
        }

        $roles = $preferSales ? self::SALES_ROLES : self::STAFF_ROLES;
        $userId = $this->availableStaffQuery($dealershipId, $roles)->value('id');

        if ($userId) {
            return (int) $userId;
        }

        if ($preferSales) {
            $userId = $this->availableStaffQuery($dealershipId, self::STAFF_ROLES)->value('id');
            if ($userId) {
                return (int) $userId;
            }
        }

        return null;
    }

    public function isUserAvailableAtDealership(User $user, int $dealershipId): bool
    {
        if (! $this->tablesReady() || $dealershipId <= 0) {
            return true;
        }

        return AssistantAdvisorAvailability::query()
            ->where('user_id', $user->id)
            ->where('dealership_id', $dealershipId)
            ->where('is_available', true)
            ->exists();
    }

    /**
     * @return list<array{dealership_id: int, dealership_name: string, is_available: bool, available_since: string|null}>
     */
    public function listForUser(User $user): array
    {
        $dealershipTable = (new Dealership)->getTable();
        $dealerships = $user->dealerships()
            ->orderBy($dealershipTable.'.name')
            ->get([$dealershipTable.'.id', $dealershipTable.'.name']);

        if ($dealerships->isEmpty()) {
            return [];
        }

        $availabilityByDealership = [];
        if ($this->tablesReady()) {
            $availabilityByDealership = AssistantAdvisorAvailability::query()
                ->where('user_id', $user->id)
                ->whereIn('dealership_id', $dealerships->pluck('id'))
                ->get()
                ->keyBy('dealership_id');
        }

        $rows = [];
        foreach ($dealerships as $dealership) {
            $record = $availabilityByDealership[$dealership->id] ?? null;
            $rows[] = [
                'dealership_id' => (int) $dealership->id,
                'dealership_name' => $dealership->name,
                'is_available' => (bool) ($record?->is_available ?? false),
                'available_since' => $record?->available_since?->toIso8601String(),
            ];
        }

        return $rows;
    }

    /**
     * @return array{dealership_id: int, is_available: bool, available_since: string|null}
     */
    public function setAvailability(User $user, int $dealershipId, bool $available): array
    {
        if (! $this->userBelongsToDealership($user, $dealershipId)) {
            throw new \InvalidArgumentException('No tienes asignada esta sucursal.');
        }

        if (! $this->tablesReady()) {
            throw new \RuntimeException('El módulo de disponibilidad no está disponible (falta migración).');
        }

        $record = AssistantAdvisorAvailability::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'dealership_id' => $dealershipId,
            ],
            [
                'is_available' => $available,
                'available_since' => $available ? now() : null,
            ]
        );

        return [
            'dealership_id' => (int) $record->dealership_id,
            'is_available' => (bool) $record->is_available,
            'available_since' => $record->available_since?->toIso8601String(),
        ];
    }

    public function unavailableHandoffMessage(string $dealershipName): string
    {
        return 'En este momento no hay asesores disponibles en '.$dealershipName
            .' para atenderte por chat. Puedes seguir consultando con el asistente virtual o intentar más tarde.';
    }

    private function userBelongsToDealership(User $user, int $dealershipId): bool
    {
        $dealershipTable = (new Dealership)->getTable();

        return $user->dealerships()
            ->where($dealershipTable.'.id', $dealershipId)
            ->exists();
    }

    /**
     * @param  list<string>  $roles
     */
    private function availableStaffQuery(int $dealershipId, array $roles)
    {
        $dealershipTable = (new Dealership)->getTable();
        $availabilityTable = (new AssistantAdvisorAvailability)->getTable();

        return User::query()
            ->select('users.id')
            ->join($availabilityTable, function ($join) use ($availabilityTable, $dealershipId) {
                $join->on('users.id', '=', $availabilityTable.'.user_id')
                    ->where($availabilityTable.'.dealership_id', '=', $dealershipId)
                    ->where($availabilityTable.'.is_available', '=', true);
            })
            ->whereHas('roles', fn ($q) => $q->whereIn('name', $roles))
            ->whereHas('dealerships', fn ($q) => $q->where($dealershipTable.'.id', $dealershipId))
            ->orderBy($availabilityTable.'.available_since')
            ->orderBy('users.id');
    }
}
