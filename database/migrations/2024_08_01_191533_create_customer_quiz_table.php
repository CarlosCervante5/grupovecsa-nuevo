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
        $tableName = env('DB_TABLE_PREFIX', '') . 'customer_quiz';

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('selected_value')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'customers')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('quiz_id')->nullable()->constrained(env('DB_TABLE_PREFIX', '') . 'quizzes')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'customer_quiz');
    }
};
