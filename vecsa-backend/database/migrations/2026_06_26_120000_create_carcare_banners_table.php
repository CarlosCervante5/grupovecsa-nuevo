<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = env('DB_TABLE_PREFIX', '').'carcare_banners';

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sort_id')->nullable();
            $table->uuid()->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('disclaimer')->nullable();
            $table->string('desktop_image_path', 500)->nullable();
            $table->string('mobile_image_path', 500)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '').'carcare_banners');
    }
};
