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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'event_multimedia', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->integer('sort_id',false,true)->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('multimedia_path');
            $table->foreignId('event_id')->constrained(env('DB_TABLE_PREFIX', '') . 'marketing_events')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'event_multimedia');
    }
};
