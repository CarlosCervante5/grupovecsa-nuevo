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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'valuation_repairs', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->text('description');
            $table->double('cost')->nullable();
            $table->string('status')->default('on_hold');
            $table->string('image_path')->nullable();
            $table->foreignId('valuation_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'vehicle_valuations')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'valuation_repairs');
    }
};
