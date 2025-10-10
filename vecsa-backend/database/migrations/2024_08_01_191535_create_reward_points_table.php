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
        Schema::create( env('DB_TABLE_PREFIX', '') . 'reward_points', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->boolean('redeemed')->default(false);
            $table->double('earned_points',false,true)->nullable();
            $table->double('purchase_amount')->nullable();
            $table->integer('initial_mileage',false,true)->nullable();
            $table->integer('final_mileage',false,true)->nullable();
            $table->string('detail')->nullable();
            $table->string('sale_id')->nullable();
            $table->string('image_path')->nullable();
            $table->foreignId('customer_reward_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'customer_reward')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'reward_points');
    }
};
