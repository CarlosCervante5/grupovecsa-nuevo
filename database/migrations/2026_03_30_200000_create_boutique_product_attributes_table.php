<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable(env('DB_TABLE_PREFIX', '') . 'boutique_product_attributes')) { return; }
        Schema::create(env('DB_TABLE_PREFIX', '') . 'boutique_product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'boutique_product_attributes');
    }
};
