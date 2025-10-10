<?php

namespace App\Http\Controllers\SpareParts;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SpareParts\DeleteSparePartRequest;
use App\Http\Requests\SpareParts\StoreSparePartRequest;
use App\Models\Valuations\ValuationPart;
use App\Models\Valuations\VehicleValuation;
use App\Services\ValuationService;

class SparePartController extends Controller
{

    protected $valuationService;

    public function __construct(ValuationService $valuationService)
    {
        $this->valuationService = $valuationService;

    }

    /**
     * Almacenar refacciones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreSparePartRequest $request)
    {
        try {

            $data = $request->validated();

            $valuation = VehicleValuation::findByUuid($data['valuation_uuid']);

            ValuationPart::create([
                'code' => $data['code'] ?? null,
                'name' => $data['name'],
                'labor_time' => $data['labor_time'],
                'quantity' => $data['quantity'],
                'valuation_id' => $valuation->id
            ]);

            // Actualizar status de partes a pending_review cada que se agregue una refaccion nueva
            $valuation->update([
                'status_parts' => 'pending_review'
            ]);

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Refaccion creada exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear la refaccion', $e->getMessage(), 500, 'SPARE_PART_CREATE_ERROR');
        }
    }


    /**
     * Eliminar refacción
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteSparePartRequest $request)
    {
        try {

            $data = $request->validated();

            $valuation_part = ValuationPart::findByUuid($data['part_uuid']);
            
            if ($valuation_part) {
                
                $valuation_part->delete();

                // Verificar si todas las valuation parts estan cotizadas y actualizar status de valuación
                $valuation = $valuation_part->valuation;

                $parts_status = $this->valuationService->statusParts($valuation);

                if( $parts_status ){

                    $valuation->update([
                        'status_parts' => 'parts_done'
                    ]);
                }

                return ApiResponseHelper::apiSuccess(200, 'Refaccion eliminada exitosamente');

            } else {
                return ApiResponseHelper::apiError('La refaccion no existe', 'No existe el id: '. $data['uuid'] ,404, 'GET_SPARE_PART_ERROR');
            }

            return ApiResponseHelper::apiSuccess(201, 'Refaccion creada exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar la refaccion', $e->getMessage(), 500, 'SPARE_PART_DELETE_ERROR');
        }
    }

}
