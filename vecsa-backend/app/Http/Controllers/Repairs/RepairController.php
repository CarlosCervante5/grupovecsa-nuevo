<?php

namespace App\Http\Controllers\Repairs;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Repairs\DeleteRepairRequest;
use App\Http\Requests\Repairs\StoreRepairRequest;
use App\Http\Requests\Repairs\UpdateRepairRequest;
use App\Jobs\UploadRepairImage;
use App\Models\Valuations\ValuationRepair;
use App\Models\Valuations\VehicleValuation;
use App\Services\ValuationService;

class RepairController extends Controller
{
    protected $valuationService;

    public function __construct(ValuationService $valuationService)
    {
        $this->valuationService = $valuationService;

    }

    /**
     * Almacenar reparaciones (Hojalatería y pintura)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRepairRequest $request)
    {
        try {

            $data = $request->validated();

            $valuation = VehicleValuation::findByUuid($data['valuation_uuid']);

            $valuation_repair = ValuationRepair::create([
                'description' => $data['description'],
                'valuation_id' => $valuation->id
            ]);

            if( isset($data['image']) ){

                $image = $request->file('image');

                $path = $image->store('temp_images');

                UploadRepairImage::dispatchSync($path, $valuation_repair, $image->getClientOriginalName());
            }

            // Actualizar status de reparaciones a pending_review cada que se agregue una reparación
            $valuation->update([
                'status_repairs' => 'pending_review'
            ]);
            
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Reparación solicitada exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al solicitar la reparacion', $e->getMessage(), 500, 'REPAIR_CREATE_ERROR');
        }
    }

    /**
     * Actualizar el estatus y el costo de la reparación
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRepairRequest $request)
    {
        try {

            $data = $request->validated();

            $valuation_repair = ValuationRepair::findByUuid($data['repair_uuid']);
            
            $valuation_repair->update([
                'cost' => $data['cost'] ?? null,
                'status' => $data['status'],
            ]);

            // Obtener repairs y validar si su status es diferente de on_hold, cambiar status de valuación a repairs_done

            $valuation = $valuation_repair->valuation;

            $repairs_status = $this->valuationService->statusRepairs($valuation);

            if( $repairs_status ){

                $valuation->update([
                    'status_repairs' => 'repairs_done'
                ]);
            }

            return ApiResponseHelper::apiSuccess(201, 'Reparacion actualizada exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar la reparacion', $e->getMessage(), 500, 'REPAIR_UPDATE_ERROR');
        }
    }


    /**
     * Eliminar reparaciones (Hojalatería y pintura)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteRepairRequest $request)
    {
        try {

            $data = $request->validated();

            $valuation_repair = ValuationRepair::findByUuid($data['repair_uuid']);
            
            if ($valuation_repair) {
                
                $valuation_repair->delete();

                // Validar que las reparaciones que quedan tengan un status diferente de on_hold y actualizar status de reparaciones de la valuacion
                $valuation = $valuation_repair->valuation;

                $repairs_status = $this->valuationService->statusRepairs($valuation);

                if( $repairs_status ){

                    $valuation->update([
                        'status_repairs' => 'repairs_done'
                    ]);

                }

                return ApiResponseHelper::apiSuccess(200, 'Reparacion eliminada exitosamente');

            } else {
                return ApiResponseHelper::apiError('La reparacion no existe', 'No existe el id: '. $data['uuid'] ,404, 'REPAIR_GET_ERROR');
            }

            return ApiResponseHelper::apiSuccess(201, 'Reparacion creada exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar la reparacion', $e->getMessage(), 500, 'REPAIR_DELETE_ERROR');
        }
    }

}
