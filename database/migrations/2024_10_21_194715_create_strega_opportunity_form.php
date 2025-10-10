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
        Schema::create(env('DB_TABLE_PREFIX_STREGA', '') . 'opportunity_form', function (Blueprint $table) {
            $table->id();
            $table->text('selected_value')->nullable();
            $table->foreignId('form_id')->nullable()->constrained(env('DB_TABLE_PREFIX_STREGA', '') . 'forms')->cascadeOnUpdate()->nullOnDelete();
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
        Schema::dropIfExists(env('DB_TABLE_PREFIX_STREGA', '') . 'opportunity_form');
    }
};
