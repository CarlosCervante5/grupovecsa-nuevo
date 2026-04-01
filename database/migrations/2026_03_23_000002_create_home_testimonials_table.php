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
        if (Schema::hasTable(env('DB_TABLE_PREFIX')) { return; }
        Schema::create(env('DB_TABLE_PREFIX', '') . 'home_testimonials', function (Blueprint $table) {
            $table->id();
            $table->integer('sort_id',false,true)->nullable();
            $table->uuid()->unique();
            $table->string('image_path', 500);
            $table->string('alt')->nullable();
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
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'home_testimonials');
    }
};
