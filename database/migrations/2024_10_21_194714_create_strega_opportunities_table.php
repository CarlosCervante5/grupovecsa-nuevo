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
        Schema::create(env('DB_TABLE_PREFIX_STREGA', '') . 'opportunities', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('type');
            $table->string('category')->nullable();
            $table->string('status')->nullable();
            $table->text('description')->nullable();
            $table->string('opportunity_id')->nullable();
            $table->string('dealership_name');
            $table->foreignId('campaign_id')->nullable()->constrained(env('DB_TABLE_PREFIX_STREGA', '') . 'opportunity_campaigns')->cascadeOnUpdate()->nullOnDelete();
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
        Schema::dropIfExists(env('DB_TABLE_PREFIX_STREGA', '') . 'opportunities');
    }
};
