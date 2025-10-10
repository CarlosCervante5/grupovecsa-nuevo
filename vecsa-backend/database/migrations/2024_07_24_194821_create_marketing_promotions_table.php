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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'marketing_promotions', function (Blueprint $table) {
            $table->id();
            $table->integer('sort_id',false,true)->nullable();
            $table->uuid()->unique();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->string('spec_sheet')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'marketing_promotions');
    }
};
