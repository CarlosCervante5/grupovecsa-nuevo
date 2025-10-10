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
        Schema::create(env('DB_TABLE_PREFIX', '') . 'marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('begin_date');
            $table->string('end_date');
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('segment_name')->nullable();
            $table->integer('visits',false,true)->nullable();
            $table->enum('page_status', ['public','clients','exclusive','unique','primary','offer','time_limit', 'other'])->default('public');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '') . 'marketing_campaigns');
    }
};
