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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'valuation_images', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->integer('sort_id',false,true)->nullable();
            $table->string('name')->nullable();
            $table->string('image_path')->nullable();
            $table->string('group_name')->nullable();
            $table->foreignId('valuation_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'vehicle_valuations')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'valuation_images');
    }
};
