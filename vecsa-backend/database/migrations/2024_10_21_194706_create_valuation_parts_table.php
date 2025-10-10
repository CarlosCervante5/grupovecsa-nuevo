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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'valuation_parts', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('code')->nullable();
            $table->string('name');
            $table->double('labor_time',10,2);
            $table->integer('quantity',false,true);
            $table->foreignId('valuation_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'vehicle_valuations')->cascadeOnUpdate()->nullOnDelete();
            $table->string('status')->default('on_hold');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'valuation_parts');
    }
};
