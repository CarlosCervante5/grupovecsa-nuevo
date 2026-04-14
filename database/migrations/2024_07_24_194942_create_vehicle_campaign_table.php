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
        $tableName = env('DB_TABLE_PREFIX', '') . 'vehicle_campaign';

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained(env('DB_TABLE_PREFIX', '') . 'marketing_campaigns')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained(env('DB_TABLE_PREFIX', '') . 'vehicles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'vehicle_campaign');
    }
};
