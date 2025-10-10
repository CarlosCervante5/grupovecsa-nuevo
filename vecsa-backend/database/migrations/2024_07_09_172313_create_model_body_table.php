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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'model_body', function (Blueprint $table) {
            $table->id();
            $table->foreignId('body_id')->constrained(env('DB_TABLE_PREFIX', '') . 'vehicle_bodies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('model_id')->constrained(env('DB_TABLE_PREFIX', '') . 'line_models')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'model_body');
    }
};
