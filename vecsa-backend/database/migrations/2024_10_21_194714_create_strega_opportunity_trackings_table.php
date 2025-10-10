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
        Schema::create(env('DB_TABLE_PREFIX_STREGA', '') . 'opportunity_trackings', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('scheduled_date');
            $table->string('type');
            $table->string('status')->default('on_hold');
            $table->foreignId('opportunity_id')->nullable()->constrained(env('DB_TABLE_PREFIX_STREGA', '') . 'opportunities')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX_STREGA', '') . 'opportunity_trackings');
    }
};
