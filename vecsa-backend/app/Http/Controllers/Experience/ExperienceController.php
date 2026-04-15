<?php

namespace App\Http\Controllers\Experience;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Jobs\UploadMarketingPostImage;
use App\Models\MarketingEvent;
use App\Models\MarketingPost;
use App\Services\WordPressExperienceImportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ExperienceController extends Controller
{
    /**
     * Obtener próximos eventos de Experience (fecha >= hoy).
     * Incluye MarketingEvent y posts marketing_posts (categoría WP tipo evento + event_begin_date).
     */
    public function upcomingEvents(Request $request)
    {
        try {
            $today = Carbon::today()->toDateString();

            $fromEvents = MarketingEvent::query()
                ->where('type', 'experience')
                ->where('begin_date', '>=', $today)
                ->orderBy('begin_date', 'asc')
                ->get()
                ->map(fn (MarketingEvent $e) => [
                    'uuid' => $e->uuid,
                    'name' => $e->name,
                    'begin_date' => Carbon::parse($e->begin_date)->toDateString(),
                    'end_date' => Carbon::parse($e->end_date)->toDateString(),
                    'description' => $e->description,
                    'location' => $e->location,
                    'image_path' => $e->image_path,
                    'type' => 'experience',
                    'source' => 'event',
                    'story_slug' => null,
                ]);

            $fromPosts = collect();
            $postTable = (new MarketingPost)->getTable();
            if (Schema::hasTable($postTable)
                && Schema::hasColumn($postTable, 'event_begin_date')
                && Schema::hasColumn($postTable, 'wp_category_label')
                && Schema::hasColumn($postTable, 'category')
                && Schema::hasColumn($postTable, 'status')) {
                $keywords = config('vecsa.experience_event_category_keywords', []);
                if ($keywords === []) {
                    $keywords = ['evento'];
                }

                $kws = array_values(array_filter(array_map(
                    fn ($k) => strtolower(trim((string) $k)),
                    $keywords
                ), fn ($k) => $k !== ''));

                if ($kws === []) {
                    $kws = ['evento'];
                }

                $q = MarketingPost::query()
                    ->where('category', 'experience')
                    ->where('status', 'published')
                    ->whereNotNull('event_begin_date')
                    ->whereDate('event_begin_date', '>=', $today);

                $q->where(function ($sub) use ($kws) {
                    foreach ($kws as $kw) {
                        $sub->orWhereRaw('LOWER(COALESCE(wp_category_label, \'\')) LIKE ?', ['%'.$kw.'%']);
                    }
                });

                $fromPosts = $q->orderBy('event_begin_date', 'asc')->get()->map(function (MarketingPost $p) {
                    $begin = $p->event_begin_date ? Carbon::parse($p->event_begin_date)->toDateString() : null;
                    $end = $p->event_end_date ? Carbon::parse($p->event_end_date)->toDateString() : $begin;

                    return [
                        'uuid' => $p->uuid,
                        'name' => $p->title,
                        'begin_date' => $begin,
                        'end_date' => $end,
                        'description' => $p->excerpt,
                        'location' => null,
                        'image_path' => $p->image_path,
                        'type' => 'experience',
                        'source' => 'post',
                        'story_slug' => $p->url_name,
                    ];
                });
            }

            $merged = $fromEvents->concat($fromPosts)
                ->sortBy('begin_date')
                ->values();

            return ApiResponseHelper::apiSuccess(200, 'Eventos obtenidos exitosamente', ['events' => $merged]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener eventos', $e->getMessage(), 500, 'GET_EXPERIENCE_EVENTS_ERROR');
        }
    }

    /**
     * Obtener galería de eventos pasados de Experience
     */
    public function pastEvents(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 8);

            $events = MarketingEvent::where('type', 'experience')
                ->where('begin_date', '<', Carbon::today()->toDateString())
                ->with('multimedia')
                ->orderBy('begin_date', 'desc')
                ->paginate($perPage);

            return ApiResponseHelper::apiSuccess(200, 'Galería obtenida exitosamente', ['gallery' => $events]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener galería', $e->getMessage(), 500, 'GET_EXPERIENCE_GALLERY_ERROR');
        }
    }

    /**
     * Obtener detalle de un evento por uuid
     */
    public function eventDetail(Request $request)
    {
        $request->validate(['uuid' => 'required|string']);

        try {
            $event = MarketingEvent::where('uuid', $request->uuid)
                ->where('type', 'experience')
                ->with('multimedia')
                ->first();

            if (! $event) {
                return ApiResponseHelper::apiError('Evento no encontrado', null, 404, 'EXPERIENCE_EVENT_NOT_FOUND');
            }

            return ApiResponseHelper::apiSuccess(200, 'Evento obtenido exitosamente', ['event' => $event]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el evento', $e->getMessage(), 500, 'GET_EXPERIENCE_EVENT_DETAIL_ERROR');
        }
    }

    /**
     * Obtener noticias/historias de Experience
     */
    public function posts(Request $request)
    {
        try {
            $perPage = max(1, min((int) $request->input('per_page', 6), 50));
            $page = max(1, (int) $request->input('page', 1));
            $table = (new MarketingPost)->getTable();

            $emptyPaginator = function () use ($request, $perPage, $page) {
                return new LengthAwarePaginator(
                    [],
                    0,
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            };

            if (! Schema::hasTable($table)) {
                return ApiResponseHelper::apiSuccess(200, 'Historias obtenidas exitosamente', ['posts' => $emptyPaginator()]);
            }

            if (! Schema::hasColumn($table, 'category') || ! Schema::hasColumn($table, 'status')) {
                return ApiResponseHelper::apiSuccess(200, 'Historias obtenidas exitosamente', ['posts' => $emptyPaginator()]);
            }

            if (! Schema::hasColumn($table, 'uuid')) {
                return ApiResponseHelper::apiSuccess(200, 'Historias obtenidas exitosamente', ['posts' => $emptyPaginator()]);
            }

            $candidates = ['id', 'uuid', 'title', 'excerpt', 'image_path', 'url_name', 'status', 'category', 'created_at'];
            $columns = array_values(array_filter($candidates, fn (string $c) => Schema::hasColumn($table, $c)));
            if ($columns === []) {
                return ApiResponseHelper::apiSuccess(200, 'Historias obtenidas exitosamente', ['posts' => $emptyPaginator()]);
            }

            $orderColumn = Schema::hasColumn($table, 'created_at') ? 'created_at' : 'id';

            $posts = MarketingPost::query()
                ->where('category', 'experience')
                ->where('status', 'published')
                ->select($columns)
                ->orderBy($orderColumn, 'desc')
                ->paginate($perPage);

            return ApiResponseHelper::apiSuccess(200, 'Historias obtenidas exitosamente', ['posts' => $posts]);
        } catch (\Throwable $e) {
            Log::error('GET_EXPERIENCE_POSTS_ERROR', [
                'message' => $e->getMessage(),
                'table' => (new MarketingPost)->getTable(),
            ]);

            return ApiResponseHelper::apiError('Error al obtener historias', $e->getMessage(), 500, 'GET_EXPERIENCE_POSTS_ERROR');
        }
    }

    /**
     * Crear evento de Experience (admin)
     */
    public function storeEvent(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'begin_date' => 'required|date',
            'end_date' => 'required|date',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            $event = MarketingEvent::create(array_merge($data, ['type' => 'experience']));

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('temp_images');
                \App\Jobs\UploadEventImage::dispatchSync($path, $event, $image->getClientOriginalName());
            }

            return ApiResponseHelper::apiSuccess(201, 'Evento creado exitosamente', ['event' => $event]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear el evento', $e->getMessage(), 500, 'CREATE_EXPERIENCE_EVENT_ERROR');
        }
    }

    /**
     * Eliminar evento de Experience (admin)
     */
    public function deleteEvent(Request $request)
    {
        $request->validate(['uuid' => 'required|string']);

        try {
            $event = MarketingEvent::where('uuid', $request->uuid)
                ->where('type', 'experience')
                ->first();

            if (! $event) {
                return ApiResponseHelper::apiError('Evento no encontrado', null, 404, 'EXPERIENCE_EVENT_NOT_FOUND');
            }

            $event->delete();

            return ApiResponseHelper::apiSuccess(200, 'Evento eliminado exitosamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar el evento', $e->getMessage(), 500, 'DELETE_EXPERIENCE_EVENT_ERROR');
        }
    }

    /**
     * Detalle público de una historia (slug o uuid).
     */
    public function postDetail(Request $request)
    {
        $request->validate([
            'uuid' => 'nullable|string',
            'slug' => 'nullable|string',
        ]);

        if (! $request->filled('uuid') && ! $request->filled('slug')) {
            return ApiResponseHelper::apiError('Indica uuid o slug', null, 422, 'EXPERIENCE_POST_DETAIL_MISSING');
        }

        try {
            $q = MarketingPost::where('category', 'experience')->where('status', 'published');

            if ($request->filled('uuid')) {
                $post = $q->where('uuid', $request->input('uuid'))->first();
            } else {
                $post = $q->where('url_name', strtolower($request->input('slug')))->first();
            }

            if (! $post) {
                return ApiResponseHelper::apiError('Historia no encontrada', null, 404, 'EXPERIENCE_POST_NOT_FOUND');
            }

            return ApiResponseHelper::apiSuccess(200, 'Historia obtenida exitosamente', ['post' => $post]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la historia', $e->getMessage(), 500, 'GET_EXPERIENCE_POST_DETAIL_ERROR');
        }
    }

    /**
     * Listado admin (paginado) de historias Experience.
     */
    public function adminStoriesSearch(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 20), 100);
        $page = max(1, (int) $request->input('page', 1));

        try {
            $table = (new MarketingPost)->getTable();

            $emptyPaginator = function () use ($request, $perPage, $page) {
                return new LengthAwarePaginator(
                    [],
                    0,
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            };

            if (! Schema::hasTable($table)) {
                return ApiResponseHelper::apiSuccess(200, 'Historias obtenidas exitosamente', ['posts' => $emptyPaginator()]);
            }

            if (! Schema::hasColumn($table, 'category')) {
                return ApiResponseHelper::apiSuccess(200, 'Historias obtenidas exitosamente', ['posts' => $emptyPaginator()]);
            }

            $orderCol = Schema::hasColumn($table, 'created_at') ? 'created_at' : 'id';

            $posts = MarketingPost::query()
                ->where('category', 'experience')
                ->orderBy($orderCol, 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return ApiResponseHelper::apiSuccess(200, 'Historias obtenidas exitosamente', ['posts' => $posts]);
        } catch (\Throwable $e) {
            Log::error('LIST_EXPERIENCE_STORIES_ERROR', [
                'message' => $e->getMessage(),
                'table' => (new MarketingPost)->getTable(),
            ]);

            return ApiResponseHelper::apiError('Error al listar historias', $e->getMessage(), 500, 'LIST_EXPERIENCE_STORIES_ERROR');
        }
    }

    /**
     * Opciones de formulario admin (categorías WP, tipo de publicación, palabras agenda).
     */
    public function adminStoriesMeta()
    {
        $options = config('vecsa.experience_story_wp_category_options', []);
        if (! is_array($options) || $options === []) {
            $options = ['Noticia', 'Evento', 'Eventos', 'Rodada', 'Comunidad', 'Lanzamiento'];
        }
        $options = array_values(array_unique(array_filter(array_map(
            static fn ($v) => is_string($v) ? trim($v) : '',
            $options
        ), static fn ($v) => $v !== '')));

        $keywords = config('vecsa.experience_event_category_keywords', ['evento']);
        if (! is_array($keywords) || $keywords === []) {
            $keywords = ['evento'];
        }

        return ApiResponseHelper::apiSuccess(200, 'Meta de historias Experience', [
            'wp_category_options' => $options,
            'event_agenda_keywords' => array_values($keywords),
            'post_types' => [
                ['value' => 'story', 'label' => 'Historia o noticia'],
                ['value' => 'event', 'label' => 'Evento (calendario público)'],
            ],
        ]);
    }

    /**
     * Crear historia Experience (admin).
     */
    public function adminStoriesStore(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:500',
            'url_name' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:2000',
            'body_html' => 'nullable|string',
            'image_url' => 'nullable|string|max:2000',
            'image' => 'nullable|image|max:8192',
            'status' => 'nullable|string|in:published,draft,unpublished',
            'event_begin_date' => 'nullable|date',
            'event_end_date' => 'nullable|date',
            'wp_category_label' => 'nullable|string|max:255',
        ]);

        try {
            $title = $data['title'];
            $slug = $data['url_name'] ?? Str::slug(Str::limit($title, 80, ''));
            $slug = strtolower(preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', $slug)));

            $eventBegin = $data['event_begin_date'] ?? null;
            $explicitLabel = array_key_exists('wp_category_label', $data)
                ? trim((string) ($data['wp_category_label'] ?? ''))
                : '';
            $explicitLabel = $explicitLabel !== '' ? $explicitLabel : null;
            $wpCategoryLabel = $explicitLabel;
            if ($wpCategoryLabel === null && $eventBegin) {
                $wpCategoryLabel = $this->defaultExperienceEventCategoryLabel();
            }

            $post = MarketingPost::create([
                'title' => $title,
                'url_name' => $slug,
                'excerpt' => $data['excerpt'] ?? null,
                'body_html' => $data['body_html'] ?? null,
                'status' => $data['status'] ?? 'published',
                'category' => 'experience',
                'image_path' => $data['image_url'] ?? null,
                'event_begin_date' => $eventBegin,
                'event_end_date' => $data['event_end_date'] ?? null,
                'wp_category_label' => $wpCategoryLabel,
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('temp_images');
                UploadMarketingPostImage::dispatchSync($path, $post, $image->getClientOriginalName());
                $post->refresh();
            }

            return ApiResponseHelper::apiSuccess(201, 'Historia creada exitosamente', ['post' => $post]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear la historia', $e->getMessage(), 500, 'CREATE_EXPERIENCE_STORY_ERROR');
        }
    }

    /**
     * Actualizar historia Experience (admin).
     */
    public function adminStoriesUpdate(Request $request)
    {
        $data = $request->validate([
            'uuid' => 'required|string',
            'title' => 'sometimes|string|max:500',
            'url_name' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:2000',
            'body_html' => 'nullable|string',
            'image_url' => 'nullable|string|max:2000',
            'image' => 'nullable|image|max:8192',
            'status' => 'nullable|string|in:published,draft,unpublished',
            'event_begin_date' => 'nullable|date',
            'event_end_date' => 'nullable|date',
            'wp_category_label' => 'nullable|string|max:255',
        ]);

        try {
            $post = MarketingPost::where('uuid', $data['uuid'])->where('category', 'experience')->first();
            if (! $post) {
                return ApiResponseHelper::apiError('Historia no encontrada', null, 404, 'EXPERIENCE_STORY_NOT_FOUND');
            }

            $updates = [];
            if (array_key_exists('title', $data)) {
                $updates['title'] = $data['title'];
            }
            if (array_key_exists('excerpt', $data)) {
                $updates['excerpt'] = $data['excerpt'];
            }
            if (array_key_exists('body_html', $data)) {
                $updates['body_html'] = $data['body_html'];
            }
            if (array_key_exists('status', $data)) {
                $updates['status'] = $data['status'];
            }
            if (array_key_exists('image_url', $data)) {
                $updates['image_path'] = $data['image_url'];
            }
            if (! empty($data['url_name'])) {
                $updates['url_name'] = strtolower(preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', $data['url_name'])));
            }
            if (array_key_exists('event_begin_date', $data)) {
                $updates['event_begin_date'] = $data['event_begin_date'];
            }
            if (array_key_exists('event_end_date', $data)) {
                $updates['event_end_date'] = $data['event_end_date'];
            }
            if (array_key_exists('wp_category_label', $data)) {
                $raw = trim((string) ($data['wp_category_label'] ?? ''));
                $updates['wp_category_label'] = $raw !== '' ? $raw : null;
            }

            $effectiveBegin = array_key_exists('event_begin_date', $updates)
                ? $updates['event_begin_date']
                : $post->event_begin_date;
            if (
                ! array_key_exists('wp_category_label', $updates)
                && $effectiveBegin
                && trim((string) ($post->wp_category_label ?? '')) === ''
            ) {
                $updates['wp_category_label'] = $this->defaultExperienceEventCategoryLabel();
            }

            if ($updates !== []) {
                $post->update($updates);
            }

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('temp_images');
                UploadMarketingPostImage::dispatchSync($path, $post, $image->getClientOriginalName());
                $post->refresh();
            }

            return ApiResponseHelper::apiSuccess(200, 'Historia actualizada exitosamente', ['post' => $post->fresh()]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar la historia', $e->getMessage(), 500, 'UPDATE_EXPERIENCE_STORY_ERROR');
        }
    }

    /**
     * Eliminar historia Experience (admin, soft delete).
     */
    public function adminStoriesDelete(Request $request)
    {
        $request->validate(['uuid' => 'required|string']);

        try {
            $post = MarketingPost::where('uuid', $request->uuid)->where('category', 'experience')->first();
            if (! $post) {
                return ApiResponseHelper::apiError('Historia no encontrada', null, 404, 'EXPERIENCE_STORY_NOT_FOUND');
            }
            $post->delete();

            return ApiResponseHelper::apiSuccess(200, 'Historia eliminada exitosamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar la historia', $e->getMessage(), 500, 'DELETE_EXPERIENCE_STORY_ERROR');
        }
    }

    /**
     * Importar historias desde WordPress (REST). Requiere autenticación.
     */
    public function adminStoriesImportWordpress(Request $request, WordPressExperienceImportService $import)
    {
        $data = $request->validate([
            'base_url' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:500',
        ]);

        try {
            $base = $data['base_url'] ?? 'https://vecsaexperience.com';
            $limit = $data['limit'] ?? null;

            $result = $import->importFromRest($base, $limit);

            return ApiResponseHelper::apiSuccess(200, 'Importación finalizada', $result);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error en importación WordPress', $e->getMessage(), 500, 'IMPORT_WORDPRESS_EXPERIENCE_ERROR');
        }
    }

    /**
     * Etiqueta de categoría compatible con config('vecsa.experience_event_category_keywords') para incluir el post en la agenda.
     */
    private function defaultExperienceEventCategoryLabel(): string
    {
        $keywords = config('vecsa.experience_event_category_keywords', ['evento']);
        $first = is_array($keywords) && $keywords !== [] ? $keywords[0] : 'evento';
        $first = is_string($first) ? trim($first) : 'evento';

        return $first !== '' ? $first : 'evento';
    }
}
