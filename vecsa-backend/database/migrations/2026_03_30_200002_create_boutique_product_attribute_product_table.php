<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable(env('DB_TABLE_PREFIX', '') . 'boutique_product_attribute_product')) { return; }
        Schema::create(env('DB_TABLE_PREFIX', '') . 'boutique_product_attribute_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('attribute_id');

            $table->foreign('product_id')
                  ->references('id')
                  ->on(env('DB_TABLE_PREFIX', '') . 'boutique_products')
                  ->onDelete('cascade');

            $table->foreign('attribute_id')
                  ->references('id')
                  ->on(env('DB_TABLE_PREFIX', '') . 'boutique_product_attributes')
                  ->onDelete('cascade');

            $table->unique(['product_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'boutique_product_attribute_product');
    }
};
