<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = env('DB_TABLE_PREFIX', '');
        $table = $prefix . 'boutique_products';
        if (! Schema::hasTable($table)) {
            return;
        }
        if (Schema::hasColumn($table, 'dealership_id')) {
            return;
        }
        Schema::table($table, function (Blueprint $table) use ($prefix) {
            $table->foreignId('dealership_id')
                ->nullable()
                ->after('category_id')
                ->constrained($prefix . 'dealerships')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        $prefix = env('DB_TABLE_PREFIX', '');
        $table = $prefix . 'boutique_products';
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'dealership_id')) {
            return;
        }
        Schema::table($table, function (Blueprint $table) {
            $table->dropForeign(['dealership_id']);
        });
    }
};
