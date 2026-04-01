<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Boutique\BoutiqueOrder;
use App\Services\Boutique\EnviacomService;
use Illuminate\Http\Request;

class BoutiqueShippingController extends Controller
{
    protected EnviacomService $enviacomService;

    public function __construct(EnviacomService $enviacomService)
    {
        $this->enviacomService = $enviacomService;
    }

    public function track(Request $request)
    {
        try {
            $orderUuid = $request->input('order_uuid');

            $order = BoutiqueOrder::findByUuid($orderUuid);
            if (!$order) {
                return ApiResponseHelper::apiError('El pedido no existe', null, 404, 'ORDER_NOT_FOUND');
            }

            $shipment = $order->shipment;
            if (!$shipment || !$shipment->tracking_number) {
                return ApiResponseHelper::apiError('No hay información de rastreo disponible', null, 400, 'NO_TRACKING_INFO');
            }

            $trackingData = $this->enviacomService->trackShipment($shipment->tracking_number);

            return ApiResponseHelper::apiSuccess(200, 'Información de rastreo obtenida exitosamente', [
                'tracking' => $trackingData,
                'tracking_number' => $shipment->tracking_number,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el rastreo', $e->getMessage(), 500, 'TRACK_SHIPMENT_ERROR');
        }
    }
}
