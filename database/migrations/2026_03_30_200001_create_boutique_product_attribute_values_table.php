<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(env('DB_TABLE_PREFIX', '') . 'boutique_product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->unsignedBigInteger('attribute_id');
            $table->string('value', 100);
            $table->string('color_hex', 20)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('attribute_id')
                  ->references('id')
                  ->on(env('DB_TABLE_PREFIX', '') . 'boutique_product_attributes')
                  ->onDelete('cascade');

            $table->unique(['attribute_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'boutique_product_attribute_values');
    }
};
