<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function prefix(): string
    {
        $fromEnv = getenv('DB_TABLE_PREFIX');
        if (is_string($fromEnv) && $fromEnv !== '') {
            return $fromEnv;
        }

        return (string) config('vecsa.db_table_prefix', '');
    }

    public function up(): void
    {
        $table = $this->prefix().'assistant_conversations';
        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            if (! Schema::hasColumn($table, 'dealership_id')) {
                $blueprint->unsignedBigInteger('dealership_id')->nullable()->after('user_id')->index();
            }
            if (! Schema::hasColumn($table, 'assigned_user_id')) {
                $blueprint->unsignedBigInteger('assigned_user_id')->nullable()->after('dealership_id')->index();
            }
        });
    }

    public function down(): void
    {
        $table = $this->prefix().'assistant_conversations';
        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            if (Schema::hasColumn($table, 'assigned_user_id')) {
                $blueprint->dropColumn('assigned_user_id');
            }
            if (Schema::hasColumn($table, 'dealership_id')) {
                $blueprint->dropColumn('dealership_id');
            }
        });
    }
};
