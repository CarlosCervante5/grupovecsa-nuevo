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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'boutique_cart_items', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('cart_id')->constrained(env('DB_TABLE_PREFIX', '') . 'boutique_carts')->onDelete('cascade');
            $table->foreignId('product_id')->constrained(env('DB_TABLE_PREFIX', '') . 'boutique_products')->onDelete('restrict');
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'boutique_cart_items');
    }
};
