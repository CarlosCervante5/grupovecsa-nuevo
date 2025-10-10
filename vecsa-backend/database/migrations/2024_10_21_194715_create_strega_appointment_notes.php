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
        Schema::create(env('DB_TABLE_PREFIX_STREGA', '') . 'appointment_notes', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->text('comment');
            $table->foreignId('appointment_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'customer_appointments')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX_STREGA', '') . 'appointment_notes');
    }
};
