<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla base marketing_posts (blog / historias Experience).
 * En producción a veces ya existía sin migración en repo; en SQLite/local no existía y la importación WP fallaba.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = env('DB_TABLE_PREFIX', '').'marketing_posts';

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->string('title', 500);
            $t->string('status', 32)->default('published');
            $t->text('excerpt')->nullable();
            $t->longText('body_html')->nullable();
            $t->string('image_path', 2000)->nullable();
            $t->string('url_name', 255);
            $t->string('category', 64)->default('general');
            $t->unsignedBigInteger('wp_import_id')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->unique('wp_import_id');
            $t->index('url_name');
            $t->index(['category', 'status']);
        });
    }

    public function down(): void
    {
        $table = env('DB_TABLE_PREFIX', '').'marketing_posts';

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::dropIfExists($table);
    }
};
