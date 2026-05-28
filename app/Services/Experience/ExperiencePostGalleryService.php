<?php

namespace App\Services\Experience;

use App\Jobs\UploadExperiencePostGalleryImage;
use App\Models\MarketingPost;
use App\Models\MarketingPostGalleryImage;
use App\Support\UploadableImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ExperiencePostGalleryService
{
    public function syncGalleryImagesFromRequest(Request $request, MarketingPost $post): void
    {
        if (! $request->hasFile('gallery_images')) {
            return;
        }

        $files = $request->file('gallery_images');
        if (! is_array($files)) {
            $files = [$files];
        }

        $maxSort = (int) MarketingPostGalleryImage::where('post_id', $post->id)->max('sort_id');
        $index = 0;

        foreach ($files as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $path = UploadableImage::storeTemp($file);
            UploadExperiencePostGalleryImage::dispatchSync(
                $path,
                $post,
                $file->getClientOriginalName(),
                $maxSort + ++$index
            );
        }
    }

    /**
     * @param  list<string>  $uuids
     */
    public function deleteGalleryImagesByUuid(MarketingPost $post, array $uuids): void
    {
        $uuids = array_values(array_filter(array_map('strval', $uuids)));
        if ($uuids === []) {
            return;
        }

        MarketingPostGalleryImage::query()
            ->where('post_id', $post->id)
            ->whereIn('uuid', $uuids)
            ->delete();
    }

    public function assertGalleryReadyForPublish(MarketingPost $post): void
    {
        $count = MarketingPostGalleryImage::where('post_id', $post->id)->count();
        if ($count < 1) {
            throw new \RuntimeException('La galería debe incluir al menos una foto además de la portada.');
        }
    }

    public static function tableReady(): bool
    {
        $prefix = config('vecsa.db_table_prefix', '');

        return Schema::hasTable($prefix.'marketing_post_gallery_images')
            && Schema::hasColumn($prefix.'marketing_posts', 'experience_post_type');
    }

    /**
     * Tarjeta unificada para la sección pública «Galería de eventos».
     *
     * @return array<string, mixed>
     */
    public static function galleryCardFromPost(MarketingPost $post): array
    {
        return [
            'uuid' => $post->uuid,
            'name' => $post->title,
            'begin_date' => $post->event_begin_date?->format('Y-m-d') ?? substr((string) $post->created_at, 0, 10),
            'image_path' => $post->image_path,
            'type' => 'experience',
            'source' => 'gallery_post',
            'story_slug' => $post->url_name,
        ];
    }
}
