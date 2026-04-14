<?php

namespace App\Services;

use App\Jobs\UploadMarketingPostImage;
use App\Models\MarketingPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WordPressExperienceImportService
{
    /**
     * Importa o actualiza historias desde la API REST de WordPress (posts publicados).
     *
     * @return array{imported: int, skipped: int, errors: array<int, string>}
     */
    public function importFromRest(string $baseUrl, ?int $maxPosts = null): array
    {
        $baseUrl = rtrim($baseUrl, '/');
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $page = 1;
        $perPage = 100;

        while (true) {
            if ($maxPosts !== null && $imported >= $maxPosts) {
                break;
            }

            $response = Http::timeout(120)
                ->withHeaders(['User-Agent' => 'GrupoVecsaExperienceImport/1.0'])
                ->get($baseUrl.'/wp-json/wp/v2/posts', [
                    'per_page' => $perPage,
                    'page' => $page,
                    'status' => 'publish',
                    '_embed' => 1,
                ]);

            if (! $response->successful()) {
                $errors[] = 'HTTP '.$response->status().' en página '.$page;
                break;
            }

            /** @var array<int, array<string, mixed>> $items */
            $items = $response->json();
            if (! is_array($items) || $items === []) {
                break;
            }

            foreach ($items as $wp) {
                if ($maxPosts !== null && $imported >= $maxPosts) {
                    break 2;
                }

                try {
                    $this->upsertFromWpPost($wp);
                    $imported++;
                } catch (\Throwable $e) {
                    $id = is_array($wp) ? ($wp['id'] ?? '?') : '?';
                    Log::warning('experience.wp_import', ['wp_id' => $id, 'error' => $e->getMessage()]);
                    $errors[] = 'Post WP '.$id.': '.$e->getMessage();
                    $skipped++;
                }
            }

            if (count($items) < $perPage) {
                break;
            }
            $page++;
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
    }

    /**
     * @param  array<string, mixed>  $wp
     */
    public function upsertFromWpPost(array $wp): MarketingPost
    {
        $wpId = (int) ($wp['id'] ?? 0);
        if ($wpId < 1) {
            throw new \InvalidArgumentException('ID de WordPress inválido');
        }

        $titleRaw = data_get($wp, 'title.rendered', '');
        $title = html_entity_decode(strip_tags((string) $titleRaw), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $slug = Str::slug((string) ($wp['slug'] ?? ''));
        if ($slug === '') {
            $slug = Str::slug(Str::limit($title, 80, ''));
        }

        $body = (string) data_get($wp, 'content.rendered', '');
        $excerpt = $this->excerptFromWp($wp);

        $image = data_get($wp, '_embedded.wp:featuredmedia.0.source_url');
        if (! is_string($image) || $image === '') {
            $image = null;
        }

        $terms = $this->parseWpTerms($wp);

        $publishedAt = null;
        if (! empty($wp['date'])) {
            try {
                $publishedAt = \Carbon\Carbon::parse($wp['date'])->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                $publishedAt = null;
            }
        }

        $hit = MarketingPost::withTrashed()->where('wp_import_id', $wpId)->first();
        if ($hit && $hit->trashed()) {
            $hit->restore();
        }

        $attrs = [
            'title' => $title,
            'url_name' => strtolower($slug),
            'excerpt' => $excerpt,
            'body_html' => $body,
            'image_path' => $image,
            'status' => 'published',
            'category' => 'experience',
            'wp_import_id' => $wpId,
        ];

        if ($this->marketingPostsHasColumn('wp_category_label')) {
            $attrs['wp_category_label'] = $terms['category_label'];
        }
        if ($this->marketingPostsHasColumn('wp_tags')) {
            $attrs['wp_tags'] = $terms['tags'];
        }

        if ($this->marketingPostsHasColumn('event_begin_date') && $this->wpCategoryLabelMatchesEventKeywords($terms['category_label'])) {
            $inferred = $this->inferEventBeginDateFromWp($publishedAt);
            if ($inferred !== null && ($hit === null || $hit->event_begin_date === null)) {
                $attrs['event_begin_date'] = $inferred;
            }
        }

        $post = MarketingPost::updateOrCreate(
            ['wp_import_id' => $wpId],
            $attrs
        );

        if ($publishedAt) {
            $post->forceFill(['created_at' => $publishedAt])->saveQuietly();
        }

        $post->refresh();

        if ($image !== null && $this->shouldMirrorFeaturedImage($post, $image)) {
            $this->mirrorFeaturedImage($post->fresh(), $image);
        }

        return $post->fresh();
    }

    /**
     * @param  array<string, mixed>  $wp
     * @return array{category_label: string|null, tags: array<int, string>}
     */
    private function parseWpTerms(array $wp): array
    {
        $categories = [];
        $tags = [];
        $embedded = $wp['_embedded'] ?? [];
        $termGroups = $embedded['wp:term'] ?? [];
        if (! is_array($termGroups)) {
            return ['category_label' => null, 'tags' => []];
        }
        foreach ($termGroups as $group) {
            if (! is_array($group)) {
                continue;
            }
            foreach ($group as $term) {
                if (! is_array($term)) {
                    continue;
                }
                $tax = (string) ($term['taxonomy'] ?? '');
                $nameRaw = $term['name'] ?? '';
                $name = html_entity_decode(strip_tags((string) $nameRaw), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $name = trim($name);
                if ($name === '') {
                    continue;
                }
                if ($tax === 'category') {
                    $categories[] = $name;
                } elseif ($tax === 'post_tag') {
                    $tags[] = $name;
                }
            }
        }

        return [
            'category_label' => $categories === [] ? null : implode(', ', $categories),
            'tags' => $tags,
        ];
    }

    private function marketingPostsHasColumn(string $column): bool
    {
        $table = (new MarketingPost)->getTable();

        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    private function isOurCdnUrl(?string $url): bool
    {
        if ($url === null || $url === '') {
            return false;
        }
        $base = rtrim((string) env('AWS_CLOUDFRONT_URL', ''), '/');
        if ($base === '') {
            return false;
        }

        return str_starts_with($url, $base);
    }

    private function shouldMirrorFeaturedImage(MarketingPost $post, string $remoteUrl): bool
    {
        if (! str_starts_with($remoteUrl, 'http')) {
            return false;
        }
        $sourceTracked = $this->marketingPostsHasColumn('wp_featured_source_url')
            ? ($post->wp_featured_source_url === $remoteUrl)
            : false;
        if ($this->isOurCdnUrl($post->image_path) && $sourceTracked) {
            return false;
        }

        return true;
    }

    private function mirrorFeaturedImage(MarketingPost $post, string $remoteUrl): void
    {
        try {
            $response = Http::timeout(120)
                ->withHeaders(['User-Agent' => 'GrupoVecsaExperienceImport/1.0'])
                ->get($remoteUrl);

            if (! $response->successful()) {
                Log::warning('experience.wp_import_image_http', [
                    'wp_id' => $post->wp_import_id,
                    'url' => $remoteUrl,
                    'status' => $response->status(),
                ]);

                return;
            }

            $body = $response->body();
            if ($body === '') {
                return;
            }

            $pathPart = (string) parse_url($remoteUrl, PHP_URL_PATH);
            $ext = strtolower(pathinfo($pathPart, PATHINFO_EXTENSION));
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $ext = 'jpg';
            }

            $relative = 'temp_images/wp_'.$post->wp_import_id.'_'.uniqid('', true).'.'.$ext;
            Storage::disk('local')->put($relative, $body);

            UploadMarketingPostImage::dispatchSync($relative, $post, basename($pathPart) ?: 'featured.'.$ext);

            $post->refresh();
            if ($this->isOurCdnUrl($post->image_path) && $this->marketingPostsHasColumn('wp_featured_source_url')) {
                $post->forceFill(['wp_featured_source_url' => $remoteUrl])->saveQuietly();
            }
        } catch (\Throwable $e) {
            Log::warning('experience.wp_import_image', [
                'wp_id' => $post->wp_import_id,
                'url' => $remoteUrl,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function wpCategoryLabelMatchesEventKeywords(?string $label): bool
    {
        if ($label === null || $label === '') {
            return false;
        }
        $lower = mb_strtolower($label, 'UTF-8');
        $keywords = config('vecsa.experience_event_category_keywords', []);
        if ($keywords === []) {
            $keywords = ['evento'];
        }
        foreach ($keywords as $kw) {
            $kw = mb_strtolower(trim((string) $kw), 'UTF-8');
            if ($kw === '') {
                continue;
            }
            if (str_contains($lower, $kw)) {
                return true;
            }
        }

        return false;
    }

    private function inferEventBeginDateFromWp(?string $publishedAt): ?string
    {
        if ($publishedAt === null || $publishedAt === '') {
            return null;
        }
        try {
            $day = \Carbon\Carbon::parse($publishedAt)->startOfDay();
            if ($day->greaterThanOrEqualTo(\Carbon\Carbon::today())) {
                return $day->toDateString();
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $wp
     */
    private function excerptFromWp(array $wp): string
    {
        $ex = (string) data_get($wp, 'excerpt.rendered', '');
        if ($ex !== '') {
            $ex = html_entity_decode(strip_tags($ex), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $ex = trim(preg_replace('/\s+/', ' ', $ex));

            return Str::limit($ex, 600, '…');
        }

        $body = (string) data_get($wp, 'content.rendered', '');
        $plain = html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = trim(preg_replace('/\s+/', ' ', $plain));

        return Str::limit($plain, 600, '…');
    }
}
