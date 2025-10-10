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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'valuation_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->integer('sort_id',false,true)->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('values')->nullable();
            $table->string('value_type')->nullable();
            $table->string('section_name')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'valuation_checkpoints');
    }
};
