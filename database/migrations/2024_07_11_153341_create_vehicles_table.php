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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'vehicles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name',90)->nullable();
            $table->text('description')->nullable();
            $table->string('vin',20)->nullable();
            $table->date('purchase_date')->nullable();
            $table->double('sale_price',10,2)->nullable();
            $table->double('list_price',10,2)->nullable();
            $table->double('offer_price',10,2)->nullable();
            $table->integer('mileage',false,true)->nullable();
            $table->enum('type', ['car', 'moto', 'truck','other'])->nullable()->default(null);
            $table->enum('category', ['new','pre_owned','demo'])->nullable()->default(null);
            $table->integer('cylinders',false,true)->nullable();
            $table->string('interior_color',90)->nullable();
            $table->string('exterior_color',90)->nullable();
            $table->enum('transmission', ['manual', 'automatic', 'semiautomatic', 'cvt', 'triptronic', 'dual-clutch'])->nullable()->default(null);
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric', 'hybrid', 'hydrogen', 'natural_gas'])->nullable()->default(null);
            $table->string('drive_train',90)->nullable();
            $table->enum('page_status',['active','inactive','sale','valuing'])->default('inactive');
            $table->string('spec_sheet')->nullable();
            $table->foreignId('brand_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'vehicle_brands')->cascadeOnUpdate()->nulllOnDelete();
            $table->foreignId('line_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'brand_lines')->cascadeOnUpdate()->nulllOnDelete();
            $table->foreignId('model_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'line_models')->cascadeOnUpdate()->nulllOnDelete();
            $table->foreignId('version_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'model_versions')->cascadeOnUpdate()->nulllOnDelete();
            $table->foreignId('body_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'vehicle_bodies')->cascadeOnUpdate()->nulllOnDelete();
            $table->foreignId('dealership_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'dealerships')->cascadeOnUpdate()->nulllOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'vehicles');
    }
};
