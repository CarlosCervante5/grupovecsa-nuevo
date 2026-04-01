<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = env('DB_TABLE_PREFIX', '') . 'marketing_posts';

        // Skip if table doesn't exist (local SQLite dev environment)
        if (!Schema::hasTable($table)) {
            return;
        }

        if (!Schema::hasColumn($table, 'category')) {
            Schema::table($table, function ($t) {
                $t->string('category')->default('general')->after('status');
            });
        }
    }

    public function down(): void
    {
        $table = env('DB_TABLE_PREFIX', '') . 'marketing_posts';

        if (!Schema::hasTable($table)) {
            return;
        }

        if (Schema::hasColumn($table, 'category')) {
            Schema::table($table, function ($t) {
                $t->dropColumn('category');
            });
        }
    }
};
