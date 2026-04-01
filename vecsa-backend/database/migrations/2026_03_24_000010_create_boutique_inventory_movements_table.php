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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'boutique_inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('product_id')->constrained(env('DB_TABLE_PREFIX', '') . 'boutique_products')->onDelete('cascade');
            $table->integer('previous_stock');
            $table->integer('new_stock');
            $table->integer('quantity_change');
            $table->string('reason', 255);
            $table->string('reference_type', 50)->nullable();
            $table->char('reference_uuid', 36)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'boutique_inventory_movements');
    }
};
