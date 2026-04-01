<?php

namespace App\Http\Controllers\Experience;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\MarketingEvent;
use App\Models\MarketingPost;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    /**
     * Obtener próximos eventos de Experience (fecha >= hoy)
     */
    public function upcomingEvents(Request $request)
    {
        try {
            $events = MarketingEvent::where('type', 'experience')
                ->where('begin_date', '>=', Carbon::today()->toDateString())
                ->orderBy('begin_date', 'asc')
                ->get();

            return ApiResponseHelper::apiSuccess(200, 'Eventos obtenidos exitosamente', ['events' => $events]);
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

            if (!$event) {
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
            $perPage = $request->input('per_page', 6);

            $posts = MarketingPost::where('category', 'experience')
                ->where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return ApiResponseHelper::apiSuccess(200, 'Historias obtenidas exitosamente', ['posts' => $posts]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener historias', $e->getMessage(), 500, 'GET_EXPERIENCE_POSTS_ERROR');
        }
    }

    /**
     * Crear evento de Experience (admin)
     */
    public function storeEvent(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'begin_date'  => 'required|date',
            'end_date'    => 'required|date',
            'description' => 'nullable|string',
            'location'    => 'nullable|string|max:255',
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

            if (!$event) {
                return ApiResponseHelper::apiError('Evento no encontrado', null, 404, 'EXPERIENCE_EVENT_NOT_FOUND');
            }

            $event->delete();

            return ApiResponseHelper::apiSuccess(200, 'Evento eliminado exitosamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar el evento', $e->getMessage(), 500, 'DELETE_EXPERIENCE_EVENT_ERROR');
        }
    }
}
