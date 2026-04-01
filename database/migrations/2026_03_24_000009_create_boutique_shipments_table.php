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
        if (Schema::hasTable(env('DB_TABLE_PREFIX', '') . 'boutique_shipments')) { return; }
        Schema::create(env('DB_TABLE_PREFIX', '') . 'boutique_shipments', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('order_id')->constrained(env('DB_TABLE_PREFIX', '') . 'boutique_orders')->onDelete('cascade');
            $table->string('delivery_method', 20);
            $table->string('carrier_name', 100)->nullable();
            $table->string('tracking_number', 255)->nullable();
            $table->string('envia_label_url', 500)->nullable();
            $table->string('envia_shipment_id', 255)->nullable();
            $table->foreignId('dealership_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'dealerships')->nullOnDelete();
            $table->string('status', 30)->default('pendiente');
            $table->date('estimated_delivery')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'boutique_shipments');
    }
};
