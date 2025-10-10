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
        Schema::create(env('DB_TABLE_PREFIX_STREGA', '') . 'contact_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('type')->default('first_contact');
            $table->string('status');
            $table->text('description')->nullable();
            $table->string('transfer_status')->nullable();
            $table->text('transfer_description')->nullable();
            $table->string('contact_channel')->nullable();
            $table->text('comments')->nullable();
            $table->foreignId('opportunity_id')->nullable()->constrained(env('DB_TABLE_PREFIX_STREGA', '') . 'opportunities')->cascadeOnUpdate()->nullOnDelete();
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
        Schema::dropIfExists(env('DB_TABLE_PREFIX_STREGA', '') . 'contact_attempts');
    }
};
