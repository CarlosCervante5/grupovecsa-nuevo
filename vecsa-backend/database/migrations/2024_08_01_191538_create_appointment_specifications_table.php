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
        $tableName = env('DB_TABLE_PREFIX', '') . 'appointment_specifications';

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('interested_vehicle_brands')->nullable();
            $table->string('interested_vehicle_models')->nullable();
            $table->string('interested_vehicle_years')->nullable();
            $table->text('comments')->nullable();
            $table->foreignId('appointment_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'customer_appointments')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'appointment_specifications');
    }
};
