<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = env('DB_TABLE_PREFIX', '') . 'marketing_posts';

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($table) {
            if (! Schema::hasColumn($table, 'excerpt')) {
                $t->text('excerpt')->nullable()->after('title');
            }
            if (! Schema::hasColumn($table, 'body_html')) {
                $t->longText('body_html')->nullable()->after('excerpt');
            }
            if (! Schema::hasColumn($table, 'wp_import_id')) {
                $t->unsignedBigInteger('wp_import_id')->nullable()->after('body_html')->unique();
            }
        });
    }

    public function down(): void
    {
        $table = env('DB_TABLE_PREFIX', '') . 'marketing_posts';

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($table) {
            if (Schema::hasColumn($table, 'wp_import_id')) {
                $t->dropUnique(['wp_import_id']);
                $t->dropColumn('wp_import_id');
            }
            if (Schema::hasColumn($table, 'body_html')) {
                $t->dropColumn('body_html');
            }
            if (Schema::hasColumn($table, 'excerpt')) {
                $t->dropColumn('excerpt');
            }
        });
    }
};
