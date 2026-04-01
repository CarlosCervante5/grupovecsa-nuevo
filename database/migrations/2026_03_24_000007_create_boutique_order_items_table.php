<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable(env('DB_TABLE_PREFIX', '') . 'boutique_order_items')) { return; }
        Schema::create(env('DB_TABLE_PREFIX', '') . 'boutique_order_items', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('order_id')->constrained(env('DB_TABLE_PREFIX', '') . 'boutique_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained(env('DB_TABLE_PREFIX', '') . 'boutique_products')->onDelete('restrict');
            $table->string('product_name', 255);
            $table->string('product_sku', 100);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'boutique_order_items');
    }
};
