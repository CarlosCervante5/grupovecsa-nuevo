<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = (string) config('vecsa.db_table_prefix', '').'assistant_conversations';

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            if (! Schema::hasColumn($table, 'visitor_phone')) {
                $blueprint->string('visitor_phone', 40)->nullable()->after('visitor_email');
            }
            if (! Schema::hasColumn($table, 'contact_callback_requested_at')) {
                $blueprint->timestamp('contact_callback_requested_at')->nullable()->after('human_handoff_at');
            }
        });
    }

    public function down(): void
    {
        $table = (string) config('vecsa.db_table_prefix', '').'assistant_conversations';

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            if (Schema::hasColumn($table, 'contact_callback_requested_at')) {
                $blueprint->dropColumn('contact_callback_requested_at');
            }
            if (Schema::hasColumn($table, 'visitor_phone')) {
                $blueprint->dropColumn('visitor_phone');
            }
        });
    }
};
