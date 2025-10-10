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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'customer_coupons', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            $table->string('code')->nullable();
            $table->double('amount')->nullable();
            $table->string('discount_type')->nullable();
            $table->text('description')->nullable();
            $table->integer('usage_count')->nullable();
            $table->boolean('individual_use')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->text('product_categories')->nullable();
            $table->text('excluded_product_categories')->nullable();
            $table->double('minimum_amount')->nullable();
            $table->double('maximum_amount')->nullable();
            $table->string('source')->nullable();

            $table->foreignId('customer_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'customers')->cascadeOnUpdate()->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'customer_coupons');
    }
};
