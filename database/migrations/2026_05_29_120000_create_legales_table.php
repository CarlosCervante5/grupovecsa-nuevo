<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = env('DB_TABLE_PREFIX', '').'legales';

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('slug', 64)->unique();
            $table->string('title');
            $table->longText('body_html');
            $table->string('meta_description', 500)->nullable();
            $table->boolean('is_published')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained(env('DB_TABLE_PREFIX', '').'users')->nullOnDelete();
            $table->timestamps();
        });

        (new \Database\Seeders\LegalesSeeder())->run();
    }

    public function down(): void
    {
        Schema::dropIfExists(env('DB_TABLE_PREFIX', '').'legales');
    }
};
