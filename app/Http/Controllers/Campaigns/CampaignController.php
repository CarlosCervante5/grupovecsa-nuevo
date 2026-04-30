<?php

namespace App\Http\Controllers\Campaigns;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Campaigns\AttachVehicleRequest;
use App\Http\Requests\Campaigns\DeleteCampaignRequest;
use App\Http\Requests\Campaigns\SearchCampaignRequest;
use App\Http\Requests\Campaigns\SearchNameCampaignRequest;
use App\Http\Requests\Campaigns\StoreCampaignRequest;
use App\Http\Requests\Campaigns\UpdatePromotionImageStorageRequest;
use App\Jobs\UpdateCampaignPromotionImage;
use App\Jobs\UploadCampaignImage;
use App\Models\MarketingCampaign;
use App\Models\Vehicle;
use App\Services\DealershipAccessService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampaignController extends Controller
{
    public function __construct(protected DealershipAccessService $dealershipAccess) {}

    /**
     * Obtener una lista de todas las campañas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $query = MarketingCampaign::with('promotions');
            $this->dealershipAccess->scopeCampaignsForInventoryUser($query, $request->user());
            $campaigns = $query->get();

            return ApiResponseHelper::apiSuccess(200, 'Campañas obtenidas exitosamente', ['campaigns' => $campaigns]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de campañas', $e->getMessage(), 500, 'GET_CAMPAIGNS_ERROR');
        }
    }

    /**
     * Obtener una lista de todas las campañas por categoría
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchCategory(Request $request)
    {
        try {
            $query = MarketingCampaign::with('promotions');
            $this->dealershipAccess->scopeCampaignsForInventoryUser($query, $request->user());
            $campaigns = $query->get()->groupBy(['category', 'segment_name']);

            return ApiResponseHelper::apiSuccess(200, 'Campañas obtenidas exitosamente', ['campaigns' => $campaigns]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de campañas', $e->getMessage(), 500, 'GET_CAMPAIGNS_ERROR');
        }
    }

    /**
     * Almacenar una campaña
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCampaignRequest $request)
    {
        try {
            
            $data = $request->validated();
            
            $campaignKeys = [
                'begin_date',
                'end_date',
                'name',
                'category',
                'description',
                'segment_name',
                'visits',
                'page_status',
            ];
            
            $campaignSubset = array_intersect_key($data, array_flip($campaignKeys));
    
            $campaign = MarketingCampaign::create($campaignSubset);


            if( isset($data['image']) ){

                $image = $request->file('image');

                $path = $image->store('temp_images');

                UploadCampaignImage::dispatchSync($path, $campaign, $image->getClientOriginalName());
            }

            return ApiResponseHelper::apiSuccess(200, 'Campaña subida exitosamente.');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al subir la campaña', $e->getMessage(), 500, 'GET_CAMPAGIN_STORE_ERROR');
        }
    }

    /**
     * Eliminar campaña mediante uuid.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteCampaignRequest $request)
    {
        try {

            $data = $request->validated();

            $campaign = MarketingCampaign::findByUuid($data['uuid']);

            if ($campaign) {
                
                $campaign->delete();

                return ApiResponseHelper::apiSuccess(200, 'Campaña eliminada exitosamente');

            } else {
                return ApiResponseHelper::apiError('La campaña no existe', 'No existe el id: '. $data['uuid'] ,404, 'GET_CAMPAIGN_ERROR');
            }

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la campaña', $e->getMessage(), 500, 'GET_CAMPAIGN_ERROR');
        }
    }

    /**
     * Obtener una lista de todas las campañas activas y sus promociones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function active()
    {
        try {
            
            $campaigns = MarketingCampaign::with('promotions')->active()->orderBy('created_at', 'asc')->get();

            return ApiResponseHelper::apiSuccess(200, 'Campañas obtenidas exitosamente', ['campaigns' => $campaigns]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de campañas', $e->getMessage(), 500, 'GET_CAMPAIGNS_ERROR');
        }
    }

    /**
     * Obtener una lista de todas las campañas activas y sus promociones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function activeByName(SearchNameCampaignRequest $request)
    {
        try {

            $data = $request->validated();

            $campaigns = MarketingCampaign::where(['name' => $data['campaign_name']])->with('promotions')->active()->get();

            return ApiResponseHelper::apiSuccess(200, 'Campañas obtenidas exitosamente', ['campaigns' => $campaigns]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de campañas', $e->getMessage(), 500, 'GET_CAMPAIGNS_ERROR');
        }
    }

    /**
     * Actualizar orden de las promociones
     *
     * @param  \App\Http\Requests\Campaigns\AttachVehicleRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachVehicle(AttachVehicleRequest $request)
    {
        try {

            $data = $request->validated();

            $vehicle = Vehicle::findByUuid($data['vehicle_uuid']);

            if (! $vehicle) {
                return ApiResponseHelper::apiError('El vehículo no existe', null, 404, 'VEHICLE_NOT_FOUND');
            }

            $user = $request->user();
            $this->dealershipAccess->assertDealershipAllowed($user, $vehicle->dealership_id);

            // Borrar campañas asociadas en caso de tenearlas

            $vehicle->campaigns()->detach();

            DB::transaction(function () use ($data, $vehicle) {
                foreach ($data['campaing_uuids'] as $uuid) {

                    $campaign = MarketingCampaign::findByUuid($uuid);

                    if ($campaign) {
                        $vehicle->campaigns()->attach($campaign->id);
                    }
                }
            });

            return ApiResponseHelper::apiSuccess(200, 'Campañas vinculadas exitosamente');

        } catch (AuthorizationException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 403, 'INVENTORY_FORBIDDEN');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al vincular promociones con vehículo', $e->getMessage(), 500, 'ATTACH_CAMPAIGN_ERROR');
        }
    }

     /**
     * Subir imagenes de un vehículo a AWS
     *
     * @param  \App\Http\Requests\Users\UploadVehicleImageRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStorage(UpdatePromotionImageStorageRequest $request)
    {
        try {
            
            $campaign_uuid = $request->input('campaign_uuid');

            $campaign = MarketingCampaign::findByUuid($campaign_uuid);

            if (!$campaign) {

                return ApiResponseHelper::apiError('La campaña no existe', 'No existe el id: '. $campaign_uuid ,404, 'CREATE_VEHICLE_IMAGES_ERROR');
            }

            $jobs_in_queue = DB::table('jobs')
                ->where('payload', 'like', '%'.$campaign_uuid.'%')
                ->exists();
            
            if ($jobs_in_queue) {
                return ApiResponseHelper::apiError('Ya hay una carga de imágenes en progreso para este vehículo. Por favor espere a que se complete.', null, 429, 'IMAGE_UPLOAD_IN_PROGRESS');
            }

            // Obtener imagenes
            $campaign_promotions = $campaign->promotions;

            foreach ($campaign_promotions as $promotion) {

                UpdateCampaignPromotionImage::dispatch($promotion, $campaign->uuid);
            }
        
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Set de imagenes enviadas a procesar');

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al actualziar el set de imagenes', $e->getMessage(), 500, 'UPDATE_CAMPAIGN_IMAGES_ERROR');
        }
    }
}
