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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'customer_vehicles', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('name',90)->nullable();
            $table->text('description')->nullable();
            $table->string('vin',20)->nullable();
            $table->integer('mileage',false,true)->nullable();
            $table->enum('type', ['car', 'moto', 'truck','other'])->nullable()->default(null);
            $table->integer('cylinders',false,true)->nullable();
            $table->string('interior_color',90)->nullable();
            $table->string('exterior_color',90)->nullable();
            $table->enum('transmission', ['manual', 'automatic', 'semiautomatic', 'cvt', 'triptronic', 'dual-clutch'])->nullable()->default(null);
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric', 'hybrid', 'hydrogen', 'natural_gas'])->nullable()->default(null);
            $table->string('drive_train',90)->nullable();
            $table->string('brand_name',30)->nullable();
            $table->string('model_name',30)->nullable();
            $table->string('line_name',30)->nullable();
            $table->string('year',30)->nullable();
            $table->string('version_name',30)->nullable();
            $table->string('body_name',30)->nullable();
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
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'customer_vehicles');
    }
};
