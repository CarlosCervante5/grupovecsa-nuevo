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
        if (Schema::hasTable(env('DB_TABLE_PREFIX')) { return; }
        Schema::create(env('DB_TABLE_PREFIX', '') . 'boutique_products', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('category_id')->constrained(env('DB_TABLE_PREFIX', '') . 'boutique_categories')->onDelete('restrict');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('sku', 100);
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'boutique_products');
    }
};
