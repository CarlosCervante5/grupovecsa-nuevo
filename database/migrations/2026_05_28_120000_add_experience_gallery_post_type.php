<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $postsTable = env('DB_TABLE_PREFIX', '').'marketing_posts';
        $galleryTable = env('DB_TABLE_PREFIX', '').'marketing_post_gallery_images';

        if (Schema::hasTable($postsTable) && ! Schema::hasColumn($postsTable, 'experience_post_type')) {
            Schema::table($postsTable, function (Blueprint $t) {
                $t->string('experience_post_type', 32)->default('story')->after('category');
            });
        }

        if (! Schema::hasTable($galleryTable)) {
            Schema::create($galleryTable, function (Blueprint $t) use ($postsTable) {
                $t->id();
                $t->uuid('uuid')->unique();
                $t->unsignedInteger('sort_id')->default(1);
                $t->string('image_path', 2000);
                $t->string('image_name', 500)->nullable();
                $t->foreignId('post_id')->constrained($postsTable)->cascadeOnUpdate()->cascadeOnDelete();
                $t->timestamps();
                $t->softDeletes();

                $t->index(['post_id', 'sort_id']);
            });
        }
    }

    public function down(): void
    {
        $postsTable = env('DB_TABLE_PREFIX', '').'marketing_posts';
        $galleryTable = env('DB_TABLE_PREFIX', '').'marketing_post_gallery_images';

        Schema::dropIfExists($galleryTable);

        if (Schema::hasTable($postsTable) && Schema::hasColumn($postsTable, 'experience_post_type')) {
            Schema::table($postsTable, function (Blueprint $t) {
                $t->dropColumn('experience_post_type');
            });
        }
    }
};
