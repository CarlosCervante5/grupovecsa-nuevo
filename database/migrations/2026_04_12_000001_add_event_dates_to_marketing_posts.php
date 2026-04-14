<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = env('DB_TABLE_PREFIX', '').'marketing_posts';

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($table) {
            if (! Schema::hasColumn($table, 'event_begin_date')) {
                $t->date('event_begin_date')->nullable()->after('wp_featured_source_url');
            }
            if (! Schema::hasColumn($table, 'event_end_date')) {
                $t->date('event_end_date')->nullable()->after('event_begin_date');
            }
        });
    }

    public function down(): void
    {
        $table = env('DB_TABLE_PREFIX', '').'marketing_posts';

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($table) {
            if (Schema::hasColumn($table, 'event_end_date')) {
                $t->dropColumn('event_end_date');
            }
            if (Schema::hasColumn($table, 'event_begin_date')) {
                $t->dropColumn('event_begin_date');
            }
        });
    }
};
