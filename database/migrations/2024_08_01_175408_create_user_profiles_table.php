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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'user_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 90);
            $table->string('last_name',90);
            $table->string('gender',90)->nullable();
            $table->string('phone_1',90)->nullable();
            $table->string('phone_2',90)->nullable();
            $table->string('picture')->nullable();
            $table->string('location',90)->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'user_profiles');
    }
};
