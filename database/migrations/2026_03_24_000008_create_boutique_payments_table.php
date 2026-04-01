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
        if (Schema::hasTable(env('DB_TABLE_PREFIX', '') . 'boutique_payments')) { return; }
        Schema::create(env('DB_TABLE_PREFIX', '') . 'boutique_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('order_id')->constrained(env('DB_TABLE_PREFIX', '') . 'boutique_orders')->onDelete('cascade');
            $table->string('method', 30);
            $table->decimal('amount', 10, 2);
            $table->string('status', 20)->default('pendiente');
            $table->string('stripe_payment_intent_id', 255)->nullable();
            $table->string('transaction_reference', 255)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'boutique_payments');
    }
};
