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
        Schema::create(env('DB_TABLE_PREFIX_STREGA', '') . 'contact_evidences', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->text('image_path')->nullable();
            $table->foreignId('attempt_id')->nullable()->constrained(env('DB_TABLE_PREFIX_STREGA', '') . 'contact_attempts')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX_STREGA', '') . 'contact_evidences');
    }
};
