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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'line_models', function (Blueprint $table) {
            $table->id();
            $table->string('name',90);
            $table->integer('year');
            $table->string('image_path')->nullable();
            $table->foreignId('line_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'brand_lines')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'line_models');
    }
};
