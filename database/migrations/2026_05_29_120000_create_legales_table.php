<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = (string) config('vecsa.db_table_prefix', env('DB_TABLE_PREFIX', ''));
        $tableName = $prefix.'legales';

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
            // Tabla users sin prefijo (convención del proyecto).
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        (new \Database\Seeders\LegalesSeeder())->run();
    }

    public function down(): void
    {
        $prefix = (string) config('vecsa.db_table_prefix', env('DB_TABLE_PREFIX', ''));
        Schema::dropIfExists($prefix.'legales');
    }
};
