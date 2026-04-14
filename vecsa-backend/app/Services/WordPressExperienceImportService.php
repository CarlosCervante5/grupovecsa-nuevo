<?php

namespace App\Services;

use App\Models\MarketingPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        $publishedAt = null;
        if (! empty($wp['date'])) {
            try {
                $publishedAt = \Carbon\Carbon::parse($wp['date'])->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                $publishedAt = null;
            }
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

        $hit = MarketingPost::withTrashed()->where('wp_import_id', $wpId)->first();
        if ($hit && $hit->trashed()) {
            $hit->restore();
        }

        $post = MarketingPost::updateOrCreate(
            ['wp_import_id' => $wpId],
            $attrs
        );

        if ($publishedAt) {
            $post->forceFill(['created_at' => $publishedAt])->saveQuietly();
        }

        return $post->fresh();
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
