<?php

namespace App\Http\Controllers\Multimedia;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Multimedia\DeleteMultimediaRequest;
use App\Http\Requests\Multimedia\StoreMultimediaRequest;
use App\Http\Requests\Multimedia\UpdateSortMultimediaRequest;
use App\Jobs\UploadEventMultimedia;
use App\Models\EventMultimedia;
use App\Models\MarketingEvent;
use Illuminate\Support\Facades\DB;

class MultimediaController extends Controller
{
    /**
     * Obtener una lista de todas las campañas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search()
    {
        try {
            
            $event_multimedia = EventMultimedia::all();

            return ApiResponseHelper::apiSuccess(200, 'Multimedia obtenida exitosamente', ['multimedia' => $event_multimedia]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista multimedia', $e->getMessage(), 500, 'GET_MULTIMEDIA_ERROR');
        }
    }

    /**
     * Almacenar multimedia
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreMultimediaRequest $request)
    {
        try {

            $event_uuid = $request->input('event_uuid');
            $multimedia = $request->file('multimedia');

            $event = MarketingEvent::findByUuid($event_uuid);

            if (!$event) {

                return ApiResponseHelper::apiError('El evento no existe', 'No existe el id: '. $event_uuid ,404, 'CREATE_EVENT_MULTIMEDIA_ERROR');
            }

            $jobs_in_queue = DB::table('jobs')
                ->where('payload', 'like', '%'.$event_uuid.'%')
                ->exists();
            
            if ($jobs_in_queue) {
                return ApiResponseHelper::apiError('Ya hay una carga multimedia en progreso para esta evento. Por favor espere a que se complete.', null, 429, 'MULTIMEDIA_UPLOAD_IN_PROGRESS');
            }

            
            // Obtener el sort_id más alto de las imágenes del vehículo, sino regresa 1
            $sort_id = $event->multimedia()->max('sort_id') + 1 ?? 1;
            $invalidMultimedia = [];

            foreach ($multimedia as $index => $file) {

                if (!$file->isValid()) {
                    $invalidMultimedia[] = $file->getClientOriginalName();
                    continue;
                }
            
                $mimeType = $file->getMimeType();
            
                $path = $file->store($mimeType === 'image/' ? 'temp_images' : 'temp_videos');
            
                UploadEventMultimedia::dispatch($path, $event_uuid, ($sort_id + $index), $file->getClientOriginalName(), $mimeType);
            
            }

            if (!empty($invalidMultimedia)) {
                return ApiResponseHelper::apiSuccess(201, 'El set multimedia se envió a procesar, pero contenía ALGUNOS archivos inválidos o corruptos', $invalidMultimedia);
            }
        
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Set multimedia enviado a procesar');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al subir los archivos multimedia', $e->getMessage(), 500, 'GET_MULTIMEDIA_UPLOAD_ERROR');
        }
    }

    /**
     * Actualizar orden de la multimedia
     *
     * @param  \App\Http\Requests\Multimedia\UpdateSortMultimediaRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sortUpdate( UpdateSortMultimediaRequest $request)
    {
        try {

            $data = $request->validated();

            DB::transaction(function () use ($data) {
                foreach ($data['multimedia_order'] as $order) {
                    EventMultimedia::where('uuid', $order['uuid'])->update(['sort_id' => $order['sort_id']]);
                }
            });

            return ApiResponseHelper::apiSuccess(200, 'Multimedia reordenada exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la multimedia', $e->getMessage(), 500, 'GET_MULTIMEDIA_ERROR');
        }
    }

    /**
     * Eliminar multimedia mediante uuid.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteMultimediaRequest $request)
    {
        try {

            $data = $request->validated();

            $event_multimedia = EventMultimedia::findByUuid($data['uuid']);

            if ($event_multimedia) {
                
                $event_multimedia->delete();

                return ApiResponseHelper::apiSuccess(200, 'Multimedia eliminada exitosamente');

            } else {
                return ApiResponseHelper::apiError('La multimedia no existe', 'No existe el id: '. $data['uuid'] ,404, 'GET_MULTIMEDIA_ERROR');
            }

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la multimedia', $e->getMessage(), 500, 'GET_MULTIMEDIA_ERROR');
        }
    }
}
