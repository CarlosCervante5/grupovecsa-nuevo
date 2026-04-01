<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        $table = env('DB_TABLE_PREFIX', '') . 'marketing_events';

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `type` ENUM('video','schedule','community','principal','experience') NOT NULL DEFAULT 'principal'");
        }
        // SQLite doesn't enforce ENUMs — no migration needed, values are stored as strings
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        $table = env('DB_TABLE_PREFIX', '') . 'marketing_events';

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `type` ENUM('video','schedule','community','principal') NOT NULL DEFAULT 'principal'");
        }
    }
};
