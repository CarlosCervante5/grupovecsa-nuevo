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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'brand_lines', function (Blueprint $table) {
            $table->id();
            $table->string('name',90);
            $table->string('image_path')->nullable();
            $table->foreignId('brand_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'vehicle_brands')->cascadeOnUpdate()->nulllOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'brand_lines');
    }
};
