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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'vehicle_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('reciever_name')->nullable();
            $table->string('driver_name')->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'vehicles')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'vehicle_locations');
    }
};
