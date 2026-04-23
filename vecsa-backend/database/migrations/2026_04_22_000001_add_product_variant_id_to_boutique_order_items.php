<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $p = env('DB_TABLE_PREFIX', '');
        $tbl = $p . 'boutique_order_items';
        if (! Schema::hasTable($tbl) || Schema::hasColumn($tbl, 'product_variant_id')) {
            return;
        }
        Schema::table($tbl, function (Blueprint $table) use ($p) {
            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained($p . 'boutique_product_variants')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        $p = env('DB_TABLE_PREFIX', '');
        $tbl = $p . 'boutique_order_items';
        if (! Schema::hasTable($tbl) || ! Schema::hasColumn($tbl, 'product_variant_id')) {
            return;
        }
        Schema::table($tbl, function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
        });
    }
};
