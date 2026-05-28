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
            if (! Schema::hasColumn($table, 'human_handoff_at')) {
                $blueprint->timestamp('human_handoff_at')->nullable()->after('assigned_user_id');
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
            if (Schema::hasColumn($table, 'human_handoff_at')) {
                $blueprint->dropColumn('human_handoff_at');
            }
        });
    }
};
