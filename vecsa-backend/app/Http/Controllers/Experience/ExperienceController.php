<?php

namespace App\Http\Controllers\Experience;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Jobs\UploadMarketingPostImage;
use App\Models\MarketingEvent;
use App\Models\MarketingPost;
use App\Services\Experience\ExperiencePostGalleryService;
use App\Services\WordPressExperienceImportService;
use App\Support\UploadableImage;
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
            $perPage = max(1, min((int) $request->input('per_page', 8), 50));
            $page = max(1, (int) $request->input('page', 1));

            $eventCards = MarketingEvent::where('type', 'experience')
                ->where('begin_date', '<', Carbon::today()->toDateString())
                ->orderBy('begin_date', 'desc')
                ->get()
                ->map(fn (MarketingEvent $e) => [
                    'uuid' => $e->uuid,
                    'name' => $e->name,
                    'begin_date' => $e->begin_date,
                    'image_path' => $e->image_path,
                    'type' => 'experience',
                    'source' => 'event',
                    'story_slug' => null,
                ]);

            $galleryCards = collect();
            if (ExperiencePostGalleryService::tableReady()) {
                $galleryCards = MarketingPost::query()
                    ->where('category', 'experience')
                    ->where('status', 'published')
                    ->where('experience_post_type', 'gallery')
                    ->where(function ($q) {
                        $q->whereDate('event_begin_date', '<', Carbon::today())
                            ->orWhereNull('event_begin_date');
                    })
                    ->orderByDesc('event_begin_date')
                    ->orderByDesc('id')
                    ->get()
                    ->map(fn (MarketingPost $p) => ExperiencePostGalleryService::galleryCardFromPost($p));
            }

            $merged = $eventCards->concat($galleryCards)
                ->sortByDesc('begin_date')
                ->values();

            $total = $merged->count();
            $items = $merged->slice(($page - 1) * $perPage, $perPage)->values();

            $paginator = new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return ApiResponseHelper::apiSuccess(200, 'Galería obtenida exitosamente', ['gallery' => $paginator]);
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

            $postsQuery = MarketingPost::query()
                ->where('category', 'experience')
                ->where('status', 'published');

            if (Schema::hasColumn($table, 'experience_post_type')) {
                $postsQuery->where(function ($q) {
                    $q->whereNull('experience_post_type')
                        ->orWhereIn('experience_post_type', ['story', 'event']);
                });
            }

            $posts = $postsQuery
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
                $path = \App\Support\UploadableImage::storeTemp($image);
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

            if ($post->isExperienceGallery() && ExperiencePostGalleryService::tableReady()) {
                $post->load('galleryImages');
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

            $postsQuery = MarketingPost::query()
                ->where('category', 'experience')
                ->orderBy($orderCol, 'desc');

            if (ExperiencePostGalleryService::tableReady()) {
                $postsQuery->withCount('galleryImages');
            }

            $posts = $postsQuery->paginate($perPage, ['*'], 'page', $page);

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

        $postTypes = [
            ['value' => 'story', 'label' => 'Historia o noticia'],
            ['value' => 'event', 'label' => 'Evento (calendario público)'],
        ];
        if (ExperiencePostGalleryService::tableReady()) {
            $postTypes[] = ['value' => 'gallery', 'label' => 'Galería de evento'];
        }

        return ApiResponseHelper::apiSuccess(200, 'Meta de historias Experience', [
            'wp_category_options' => $options,
            'event_agenda_keywords' => array_values($keywords),
            'post_types' => $postTypes,
            'gallery_ready' => ExperiencePostGalleryService::tableReady(),
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
            'image' => ['nullable', 'file', 'uploadable_image', 'max:8192'],
            'experience_post_type' => 'nullable|string|in:story,event,gallery',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => ['file', 'uploadable_image', 'max:8192'],
            'status' => 'nullable|string|in:published,draft,unpublished',
            'event_begin_date' => 'nullable|date',
            'event_end_date' => 'nullable|date',
            'wp_category_label' => 'nullable|string|max:255',
        ]);

        try {
            if ($schemaMsg = $this->marketingPostsTableReadyForExperienceAdmin()) {
                return ApiResponseHelper::apiError(
                    'Base de datos incompleta para historias Experience',
                    $schemaMsg,
                    503,
                    'EXPERIENCE_STORIES_SCHEMA_MISSING'
                );
            }

            $title = $data['title'];
            $slug = $data['url_name'] ?? Str::slug(Str::limit($title, 80, ''));
            $slug = strtolower(preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', $slug)));
            if ($slug === '') {
                $slug = 'experience-' . bin2hex(random_bytes(4));
            }

            $postType = $this->normalizeExperiencePostType($data['experience_post_type'] ?? 'story');
            $eventBegin = $data['event_begin_date'] ?? null;
            $status = $data['status'] ?? 'published';
            try {
                $this->validateExperiencePostTypeRules($postType, $eventBegin, $status);
            } catch (\InvalidArgumentException $e) {
                return ApiResponseHelper::apiError($e->getMessage(), null, 422, 'EXPERIENCE_POST_VALIDATION');
            }

            $explicitLabel = array_key_exists('wp_category_label', $data)
                ? trim((string) ($data['wp_category_label'] ?? ''))
                : '';
            $explicitLabel = $explicitLabel !== '' ? $explicitLabel : null;
            $wpCategoryLabel = $explicitLabel;
            if ($wpCategoryLabel === null && $eventBegin) {
                $wpCategoryLabel = $this->defaultExperienceEventCategoryLabel();
            }

            if ($postType === 'gallery' && ! ExperiencePostGalleryService::tableReady()) {
                return ApiResponseHelper::apiError(
                    'Galería no disponible en este servidor',
                    'Ejecuta las migraciones del backend (experience_post_type y marketing_post_gallery_images).',
                    503,
                    'EXPERIENCE_GALLERY_SCHEMA_MISSING'
                );
            }

            $post = MarketingPost::create($this->buildExperiencePostAttributes(
                $title,
                $slug,
                $data,
                $status,
                $eventBegin,
                $wpCategoryLabel,
                $postType
            ));

            $this->syncFeaturedImageFromRequest($request, $post);
            if (ExperiencePostGalleryService::tableReady()) {
                $this->galleryService()->syncGalleryImagesFromRequest($request, $post->fresh());
            }

            if ($postType === 'gallery' && $status === 'published' && ExperiencePostGalleryService::tableReady()) {
                $this->galleryService()->assertGalleryReadyForPublish($post->fresh());
            }

            return ApiResponseHelper::apiSuccess(201, 'Historia creada exitosamente', [
                'post' => $this->formatAdminExperiencePost($post->fresh()),
            ]);
        } catch (\Throwable $e) {
            Log::error('CREATE_EXPERIENCE_STORY_ERROR', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

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
            'image' => ['nullable', 'file', 'uploadable_image', 'max:8192'],
            'experience_post_type' => 'nullable|string|in:story,event,gallery',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => ['file', 'uploadable_image', 'max:8192'],
            'gallery_delete_uuids' => 'nullable|array',
            'gallery_delete_uuids.*' => 'string',
            'status' => 'nullable|string|in:published,draft,unpublished',
            'event_begin_date' => 'nullable|date',
            'event_end_date' => 'nullable|date',
            'wp_category_label' => 'nullable|string|max:255',
        ]);

        try {
            if ($schemaMsg = $this->marketingPostsTableReadyForExperienceAdmin()) {
                return ApiResponseHelper::apiError(
                    'Base de datos incompleta para historias Experience',
                    $schemaMsg,
                    503,
                    'EXPERIENCE_STORIES_SCHEMA_MISSING'
                );
            }

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
            if (array_key_exists('image_url', $data) && ! $request->hasFile('image')) {
                $externalUrl = trim((string) ($data['image_url'] ?? ''));
                if ($externalUrl !== '') {
                    $updates['image_path'] = $externalUrl;
                }
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
            if (array_key_exists('experience_post_type', $data) && ExperiencePostGalleryService::tableReady()) {
                $updates['experience_post_type'] = $this->normalizeExperiencePostType($data['experience_post_type']);
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

            $effectiveType = $updates['experience_post_type'] ?? $post->experience_post_type ?? 'story';
            $effectiveStatus = $updates['status'] ?? $post->status;
            try {
                $this->validateExperiencePostTypeRules(
                    $effectiveType,
                    $effectiveBegin?->format('Y-m-d') ?? (is_string($effectiveBegin) ? $effectiveBegin : null),
                    $effectiveStatus
                );
            } catch (\InvalidArgumentException $e) {
                return ApiResponseHelper::apiError($e->getMessage(), null, 422, 'EXPERIENCE_POST_VALIDATION');
            }

            if ($effectiveType === 'gallery' && ! ExperiencePostGalleryService::tableReady()) {
                return ApiResponseHelper::apiError(
                    'Galería no disponible en este servidor',
                    'Ejecuta las migraciones del backend (experience_post_type y marketing_post_gallery_images).',
                    503,
                    'EXPERIENCE_GALLERY_SCHEMA_MISSING'
                );
            }

            if ($updates !== []) {
                $post->update($updates);
            }

            if (ExperiencePostGalleryService::tableReady()) {
                if ($request->filled('gallery_delete_uuids')) {
                    $this->galleryService()->deleteGalleryImagesByUuid($post, $request->input('gallery_delete_uuids', []));
                }
                $this->galleryService()->syncGalleryImagesFromRequest($request, $post->fresh());
            }

            $this->syncFeaturedImageFromRequest($request, $post);

            $post = $post->fresh();
            if ($post->isExperienceGallery() && $post->status === 'published' && ExperiencePostGalleryService::tableReady()) {
                $this->galleryService()->assertGalleryReadyForPublish($post);
            }

            return ApiResponseHelper::apiSuccess(200, 'Historia actualizada exitosamente', [
                'post' => $this->formatAdminExperiencePost($post),
            ]);
        } catch (\Throwable $e) {
            Log::error('UPDATE_EXPERIENCE_STORY_ERROR', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

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
            if ($schemaMsg = $this->marketingPostsTableReadyForExperienceAdmin()) {
                return ApiResponseHelper::apiError(
                    'Base de datos incompleta para historias Experience',
                    $schemaMsg,
                    503,
                    'EXPERIENCE_STORIES_SCHEMA_MISSING'
                );
            }

            $post = MarketingPost::where('uuid', $request->uuid)->where('category', 'experience')->first();
            if (! $post) {
                return ApiResponseHelper::apiError('Historia no encontrada', null, 404, 'EXPERIENCE_STORY_NOT_FOUND');
            }
            $post->delete();

            return ApiResponseHelper::apiSuccess(200, 'Historia eliminada exitosamente');
        } catch (\Throwable $e) {
            Log::error('DELETE_EXPERIENCE_STORY_ERROR', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

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
     * Comprueba que exista la tabla y columnas mínimas para CRUD admin de historias Experience.
     * Devuelve mensaje humano si falta algo; null si está listo.
     */
    private function marketingPostsTableReadyForExperienceAdmin(): ?string
    {
        $table = (new MarketingPost)->getTable();

        if (! Schema::hasTable($table)) {
            return "La tabla `{$table}` no existe. En el servidor sandbox ejecuta `php artisan migrate` (o despliega las migraciones que crean marketing_posts).";
        }

        foreach (
            [
                'title',
                'url_name',
                'status',
                'category',
                'excerpt',
                'body_html',
                'image_path',
                'event_begin_date',
                'event_end_date',
                'wp_category_label',
            ] as $col
        ) {
            if (! Schema::hasColumn($table, $col)) {
                return "La tabla `{$table}` no tiene la columna `{$col}`. Ejecuta las migraciones pendientes del backend.";
            }
        }

        if (! Schema::hasColumn($table, 'experience_post_type')) {
            return 'Falta la columna `experience_post_type`. Ejecuta la migración `2026_05_28_120000_add_experience_gallery_post_type` (php artisan migrate en el servidor).';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function buildExperiencePostAttributes(
        string $title,
        string $slug,
        array $data,
        string $status,
        ?string $eventBegin,
        ?string $wpCategoryLabel,
        string $postType
    ): array {
        $attrs = [
            'title' => $title,
            'url_name' => $slug,
            'excerpt' => $data['excerpt'] ?? null,
            'body_html' => $data['body_html'] ?? null,
            'status' => $status,
            'category' => 'experience',
            'image_path' => $data['image_url'] ?? null,
            'event_begin_date' => $eventBegin,
            'event_end_date' => $data['event_end_date'] ?? null,
            'wp_category_label' => $wpCategoryLabel,
        ];

        $table = (new MarketingPost)->getTable();
        if (Schema::hasColumn($table, 'experience_post_type')) {
            $attrs['experience_post_type'] = ExperiencePostGalleryService::tableReady()
                ? $postType
                : 'story';
        }

        return $attrs;
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

    /**
     * Sube imagen destacada multipart y actualiza image_path; falla si el archivo no se procesó.
     */
    private function syncFeaturedImageFromRequest(Request $request, MarketingPost $post): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        $image = $request->file('image');
        $path = UploadableImage::storeTemp($image);
        UploadMarketingPostImage::dispatchSync($path, $post, $image->getClientOriginalName());
        $post->refresh();

        if (trim((string) ($post->image_path ?? '')) === '') {
            throw new \RuntimeException(
                'La imagen no se guardó en el servidor. Revise Cloudinary/S3 o el formato del archivo.'
            );
        }
    }

    private function galleryService(): ExperiencePostGalleryService
    {
        return app(ExperiencePostGalleryService::class);
    }

    private function normalizeExperiencePostType(?string $type): string
    {
        $type = strtolower(trim((string) $type));

        return in_array($type, ['story', 'event', 'gallery'], true) ? $type : 'story';
    }

    private function validateExperiencePostTypeRules(string $postType, ?string $eventBegin, string $status): void
    {
        if ($status !== 'published') {
            return;
        }

        if ($postType === 'event' && ! $eventBegin) {
            throw new \InvalidArgumentException('Para publicar un evento indica la fecha de inicio.');
        }

        if ($postType === 'gallery' && ! $eventBegin) {
            throw new \InvalidArgumentException('Para publicar una galería indica la fecha del evento.');
        }
    }

    private function formatAdminExperiencePost(MarketingPost $post): MarketingPost
    {
        if ($post->isExperienceGallery() && ExperiencePostGalleryService::tableReady()) {
            $post->load('galleryImages');
        }

        return $post;
    }

    private function formatDateForValidation(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('Y-m-d');
        }

        return substr((string) $value, 0, 10) ?: null;
    }
}
