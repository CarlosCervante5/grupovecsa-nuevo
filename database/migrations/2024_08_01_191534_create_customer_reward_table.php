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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'customer_reward', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('status')->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'customer_vehicles')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('reward_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'rewards')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'customers')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'customer_reward');
    }
};
