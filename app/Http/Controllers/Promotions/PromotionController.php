<?php

namespace App\Http\Controllers\Promotions;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Promotions\DeletePromotionRequest;
use App\Http\Requests\Promotions\StorePromotionRequest;
use App\Http\Requests\Promotions\UpdateSortPromotionRequest;
use App\Jobs\UploadPromotionImage;
use App\Models\MarketingCampaign;
use App\Models\MarketingPromotion;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    /**
     * Obtener una lista de todas las campañas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search()
    {
        try {
            
            $promotions = MarketingPromotion::all();

            return ApiResponseHelper::apiSuccess(200, 'Promociones obtenidas exitosamente', ['promotions' => $promotions]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de promociones', $e->getMessage(), 500, 'GET_PROMOTIONS_ERROR');
        }
    }

    /**
     * Almacenar promociones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePromotionRequest $request)
    {
        try {

            $campaign_uuid = $request->input('campaign_uuid');
            $images = $request->file('images');
            $name = $request->input('name');
            $spec_sheet = $request->input('spec_sheet');

            $campaign = MarketingCampaign::findByUuid($campaign_uuid);

            if (!$campaign) {

                return ApiResponseHelper::apiError('La campaña no existe', 'No existe el id: '. $campaign_uuid ,404, 'CREATE_CAMPAIGN_IMAGES_ERROR');
            }

            $jobs_in_queue = DB::table('jobs')
                ->where('payload', 'like', '%'.$campaign_uuid.'%')
                ->exists();
            
            if ($jobs_in_queue) {
                return ApiResponseHelper::apiError('Ya hay una carga de imágenes en progreso para esta campaña. Por favor espere a que se complete.', null, 429, 'IMAGE_UPLOAD_IN_PROGRESS');
            }

            
            // Obtener el sort_id más alto de las imágenes del vehículo, sino regresa 1
            $sort_id = $campaign->promotions()->max('sort_id') + 1 ?? 1;
            $invalidImages = [];

            foreach ($images as $index => $image) {

                // Validar si el archivo es válido
                if (!$image->isValid()) {
                    // Registrar la imagen inválida
                    $invalidImages[] = $image->getClientOriginalName();
                    continue; // Saltar a la siguiente iteración del bucle
                }

                // Guardar temporalmente el archivo
                $path = $image->store('temp_images');


                // Enviar cada lote a una cola de trabajo para procesamiento en segundo plano
                UploadPromotionImage::dispatch($path, $name, $campaign_uuid, ($sort_id + $index), $image->getClientOriginalName(), $spec_sheet);
            }

            if (!empty($invalidImages)) {
                return ApiResponseHelper::apiSuccess(201, 'El set de imagenes se envió a procesar, pero contenía ALGUNOS archivos inválidos o corruptos', $invalidImages);
            }
        
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Set de imagenes enviadas a procesar');


        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al subir las promociones', $e->getMessage(), 500, 'GET_PROMOTION_UPLOAD_ERROR');
        }
    }

    /**
     * Actualizar orden de las promociones
     *
     * @param  \App\Http\Requests\Promotions\UpdateSortPromotionRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sortUpdate( UpdateSortPromotionRequest $request)
    {
        try {

            $data = $request->validated();

            DB::transaction(function () use ($data) {
                foreach ($data['image_order'] as $order) {
                    MarketingPromotion::where('uuid', $order['uuid'])->update(['sort_id' => $order['sort_id']]);
                }
            });

            return ApiResponseHelper::apiSuccess(200, 'Promociones reordenadas exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener las promociones', $e->getMessage(), 500, 'GET_PROMOTIONS_ERROR');
        }
    }

    /**
     * Eliminar promocion mediante uuid.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeletePromotionRequest $request)
    {
        try {

            $data = $request->validated();

            $promotion = MarketingPromotion::findByUuid($data['uuid']);

            if ($promotion) {
                
                $promotion->delete();

                return ApiResponseHelper::apiSuccess(200, 'Promocion eliminada exitosamente');

            } else {
                return ApiResponseHelper::apiError('La promocion no existe', 'No existe el id: '. $data['uuid'] ,404, 'GET_PROMOTION_ERROR');
            }

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la promocion', $e->getMessage(), 500, 'GET_CAMPAIGN_ERROR');
        }
    }
}
