<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = env('DB_TABLE_PREFIX', '').'marketing_posts';

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($table) {
            if (! Schema::hasColumn($table, 'wp_category_label')) {
                $t->string('wp_category_label', 500)->nullable()->after('category');
            }
            if (! Schema::hasColumn($table, 'wp_tags')) {
                $t->json('wp_tags')->nullable()->after('wp_category_label');
            }
            if (! Schema::hasColumn($table, 'wp_featured_source_url')) {
                $t->string('wp_featured_source_url', 2000)->nullable()->after('image_path');
            }
        });
    }

    public function down(): void
    {
        $table = env('DB_TABLE_PREFIX', '').'marketing_posts';

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($table) {
            if (Schema::hasColumn($table, 'wp_featured_source_url')) {
                $t->dropColumn('wp_featured_source_url');
            }
            if (Schema::hasColumn($table, 'wp_tags')) {
                $t->dropColumn('wp_tags');
            }
            if (Schema::hasColumn($table, 'wp_category_label')) {
                $t->dropColumn('wp_category_label');
            }
        });
    }
};
