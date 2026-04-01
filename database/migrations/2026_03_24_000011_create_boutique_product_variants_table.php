<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable(env('DB_TABLE_PREFIX')) { return; }
        Schema::create(env('DB_TABLE_PREFIX', '') . 'boutique_product_variants', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('product_id')->constrained(env('DB_TABLE_PREFIX', '') . 'boutique_products')->onDelete('cascade');
            $table->string('color', 100)->nullable();
            $table->string('color_hex', 20)->nullable();
            $table->string('size', 20)->nullable();
            $table->string('sku', 100)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'boutique_product_variants');
    }
};
