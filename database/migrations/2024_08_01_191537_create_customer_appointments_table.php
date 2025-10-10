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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'customer_appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('type');
            $table->text('description')->nullable();
            $table->string('scheduled_date');
            $table->string('attendance_date')->nullable();
            $table->string('departure_date')->nullable();
            $table->string('dealership_name')->nullable();
            $table->string('status')->default('on_hold');
            $table->text('comments')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'customers')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'customer_vehicles')->cascadeOnUpdate()->nullOnDelete();
            $table->unsignedBigInteger('prev_appointment_id')->nullable();
            $table->foreign('prev_appointment_id')->references('id')->on(env('DB_TABLE_PREFIX', '') . 'customer_appointments')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'customer_appointments');
    }
};
