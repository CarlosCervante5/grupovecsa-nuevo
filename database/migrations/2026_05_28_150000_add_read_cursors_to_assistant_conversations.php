<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = (string) config('vecsa.db_table_prefix', '');
        $table = $prefix.'assistant_conversations';

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            if (! Schema::hasColumn($table, 'staff_last_read_message_id')) {
                $blueprint->unsignedBigInteger('staff_last_read_message_id')->nullable()->after('human_handoff_at');
            }
            if (! Schema::hasColumn($table, 'visitor_last_read_message_id')) {
                $blueprint->unsignedBigInteger('visitor_last_read_message_id')->nullable()->after('staff_last_read_message_id');
            }
        });
    }

    public function down(): void
    {
        $prefix = (string) config('vecsa.db_table_prefix', '');
        $table = $prefix.'assistant_conversations';

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            if (Schema::hasColumn($table, 'visitor_last_read_message_id')) {
                $blueprint->dropColumn('visitor_last_read_message_id');
            }
            if (Schema::hasColumn($table, 'staff_last_read_message_id')) {
                $blueprint->dropColumn('staff_last_read_message_id');
            }
        });
    }
};
