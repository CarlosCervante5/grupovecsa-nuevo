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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'valuation_checkpoint', function (Blueprint $table) {
            $table->id();
            $table->string('selected_value', 1000)->nullable();
            $table->foreignId('valuation_id')->constrained(env('DB_TABLE_PREFIX', '') . 'vehicle_valuations')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('checkpoint_id')->constrained(env('DB_TABLE_PREFIX', '') . 'valuation_checkpoints')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'valuation_checkpoint');
    }
};
