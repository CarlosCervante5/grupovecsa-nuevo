<?php

namespace App\Http\Controllers\Events;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\DeleteEventRequest;
use App\Http\Requests\Events\StoreEventRequest;
use App\Jobs\UploadEventImage;
use App\Models\MarketingEvent;
use Illuminate\Http\Request;

class EventController extends Controller
{   

   /**
     * Almacenar un evento
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreEventRequest $request)
    {
        try {
            
            $data = $request->validated();
            
            $eventKeys = [
                'begin_date',
                'end_date',
                'name',
                'type',
                'description',
                'segment_name',
                'page_status',
            ];
            
            $eventSubset = array_intersect_key($data, array_flip($eventKeys));
    
            $event = MarketingEvent::create($eventSubset);


            if( isset($data['image']) ){

                $image = $request->file('image');

                $path = $image->store('temp_images');

                UploadEventImage::dispatchSync($path, $event, $image->getClientOriginalName());
            }

            return ApiResponseHelper::apiSuccess(200, 'Evento subido exitosamente.');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al subir el evento', $e->getMessage(), 500, 'GET_EVENT_STORE_ERROR');
        }
    }

    /**
     * Eliminar evento mediante uuid.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteEventRequest $request)
    {
        try {

            $data = $request->validated();

            $event = MarketingEvent::findByUuid($data['uuid']);

            if ($event) {
                
                $event->delete();

                return ApiResponseHelper::apiSuccess(200, 'Evento eliminado exitosamente');

            } else {
                return ApiResponseHelper::apiError('El evento no existe', 'No existe el id: '. $data['uuid'] ,404, 'GET_EVENT_ERROR');
            }

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el evento', $e->getMessage(), 500, 'GET_EVENT_ERROR');
        }
    }

    /**
     * Obtener una lista de todas los eventos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {

        $data = $request->validate([
            'type' => 'required|in:video,schedule,community,principal',
        ]);

        try {
            
            $events = MarketingEvent::where('type', $data['type'])->with('multimedia')->get();

            return ApiResponseHelper::apiSuccess(200, 'Eventos obtenidos exitosamente', ['events' => $events]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de eventos', $e->getMessage(), 500, 'GET_EVENTS_ERROR');
        }
    }
}
