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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'vehicle_valuations', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->double('book_trade_in_offer',10,2)->nullable();
            $table->double('book_sale_price',10,2)->nullable();
            $table->double('intellimotors_trade_in_offer',10,2)->nullable();
            $table->double('intellimotors_sale_price',10,2)->nullable();
            $table->double('labor_cost',10,2)->nullable();
            $table->double('spare_parts_cost',10,2)->nullable();
            $table->double('body_work_painting_cost',10,2)->nullable();
            $table->double('estimated_total',10,2)->nullable();
            $table->double('trade_in_final',10,2)->nullable();
            $table->double('final_offer',10,2)->nullable();
            $table->string('status')->default('to_appraise');
            $table->string('status_repairs')->default('pending_entry');
            $table->string('status_parts')->default('pending_entry');
            $table->string('status_acquisition')->default('to_acquire');
            $table->string('acquisition_pdf')->nullable();
            $table->text('comments')->nullable();
            $table->text('take_type')->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'vehicles')->cascadeOnUpdate()->nulllOnDelete();
            $table->foreignId('dealership_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'dealerships')->cascadeOnUpdate()->nulllOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'customer_appointments')->cascadeOnUpdate()->nulllOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'vehicle_valuations');
    }
};
