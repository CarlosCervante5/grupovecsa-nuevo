<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable(env('DB_TABLE_PREFIX')) { return; }
        Schema::create(env('DB_TABLE_PREFIX', '') . 'boutique_variant_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->unsignedBigInteger('attribute_value_id');

            $table->foreign('variant_id')
                  ->references('id')
                  ->on(env('DB_TABLE_PREFIX', '') . 'boutique_product_variants')
                  ->onDelete('cascade');

            $table->foreign('attribute_value_id')
                  ->references('id')
                  ->on(env('DB_TABLE_PREFIX', '') . 'boutique_product_attribute_values')
                  ->onDelete('cascade');

            $table->unique(['variant_id', 'attribute_value_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'boutique_variant_attribute_values');
    }
};
