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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'campaign_promotion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained(env('DB_TABLE_PREFIX', '') . 'marketing_campaigns')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('promotion_id')->constrained(env('DB_TABLE_PREFIX', '') . 'marketing_promotions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'campaign_promotion');
    }
};
