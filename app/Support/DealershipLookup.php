<?php

namespace App\Support;

use App\Models\Dealership;
use Illuminate\Support\Facades\Schema;

/** Resuelve sucursal por id numérico o uuid (si existe la columna). */
final class DealershipLookup
{
    public static function findByUuidOrId(mixed $value): ?Dealership
    {
        $key = trim((string) $value);
        if ($key === '') {
            return null;
        }

        if (ctype_digit($key)) {
            return Dealership::find((int) $key);
        }

        $table = (new Dealership)->getTable();
        if (Schema::hasColumn($table, 'uuid')) {
            return Dealership::where('uuid', $key)->first();
        }

        return null;
    }
}
