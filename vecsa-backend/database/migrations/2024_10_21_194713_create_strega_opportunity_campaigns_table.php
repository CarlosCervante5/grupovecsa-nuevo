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
        $tableName = env('DB_TABLE_PREFIX_STREGA', '') . 'opportunity_campaigns';

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('name');
            $table->string('source')->nullable();
            $table->string('channel')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX_STREGA', '') . 'opportunity_campaigns');
    }
};
