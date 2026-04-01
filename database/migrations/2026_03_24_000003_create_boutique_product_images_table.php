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
        if (Schema::hasTable(env('DB_TABLE_PREFIX', '') . 'boutique_product_images')) { return; }
        Schema::create(env('DB_TABLE_PREFIX', '') . 'boutique_product_images', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('product_id')->constrained(env('DB_TABLE_PREFIX', '') . 'boutique_products')->onDelete('cascade');
            $table->string('image_path', 500);
            $table->string('cloudinary_public_id', 500)->nullable();
            $table->unsignedInteger('sort_id')->default(0);
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'boutique_product_images');
    }
};
