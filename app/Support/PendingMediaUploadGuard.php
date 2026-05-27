<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Evita bloqueos permanentes por jobs huérfanos en la cola (p. ej. sin queue:work).
 */
final class PendingMediaUploadGuard
{
    /** Jobs más antiguos se consideran atascados y se eliminan antes de comprobar la cola. */
    private const STALE_MINUTES = 30;

    public static function hasPendingVehicleImageUpload(string $vehicleUuid): bool
    {
        self::pruneStaleVehicleImageJobs($vehicleUuid);

        return self::pendingJobsQuery($vehicleUuid, [
            'UploadVehicleImage',
            'UpdateVehicleImage',
        ])->exists();
    }

    public static function pruneStaleVehicleImageJobs(string $vehicleUuid): int
    {
        return self::deleteStaleJobs($vehicleUuid, [
            'UploadVehicleImage',
            'UpdateVehicleImage',
        ]);
    }

    /**
     * @param  list<string>  $jobClassNames  Sufijo del FQCN del job (p. ej. UploadVehicleImage)
     */
    private static function pendingJobsQuery(string $entityUuid, array $jobClassNames): Builder
    {
        return DB::table('jobs')->where(function (Builder $query) use ($entityUuid, $jobClassNames) {
            foreach ($jobClassNames as $className) {
                $query->orWhere(function (Builder $inner) use ($entityUuid, $className) {
                    $inner->where('payload', 'like', '%'.$className.'%')
                        ->where('payload', 'like', '%'.$entityUuid.'%');
                });
            }
        });
    }

    /**
     * @param  list<string>  $jobClassNames
     */
    private static function deleteStaleJobs(string $entityUuid, array $jobClassNames): int
    {
        $cutoff = now()->subMinutes(self::STALE_MINUTES)->getTimestamp();

        return DB::table('jobs')
            ->where('created_at', '<', $cutoff)
            ->where(function (Builder $query) use ($entityUuid, $jobClassNames) {
                foreach ($jobClassNames as $className) {
                    $query->orWhere(function (Builder $inner) use ($entityUuid, $className) {
                        $inner->where('payload', 'like', '%'.$className.'%')
                            ->where('payload', 'like', '%'.$entityUuid.'%');
                    });
                }
            })
            ->delete();
    }
}
