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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'home_slides', function (Blueprint $table) {
            $table->id();
            $table->integer('sort_id',false,true)->nullable();
            $table->uuid()->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('offer_main', 100)->nullable();
            $table->string('offer_main_text')->nullable();
            $table->string('offer_sub')->nullable();
            $table->string('offer_secondary', 100)->nullable();
            $table->string('offer_secondary_text')->nullable();
            $table->string('button_text')->nullable()->default('Más Información');
            $table->string('button_link', 500)->nullable();
            $table->text('disclaimer')->nullable();
            $table->string('desktop_image_path', 500)->nullable();
            $table->string('mobile_image_path', 500)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'home_slides');
    }
};
