<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(env('DB_TABLE_PREFIX', '') . 'boutique_banners', function (Blueprint $table) {
            $table->id();
            $table->integer('sort_id', false, true)->nullable();
            $table->uuid()->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('cta_text')->nullable()->default('Explorar');
            $table->string('cta_link', 500)->nullable();
            $table->string('bg_class', 100)->nullable();
            $table->string('desktop_image_path', 500)->nullable();
            $table->string('mobile_image_path', 500)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'boutique_banners');
    }
};
