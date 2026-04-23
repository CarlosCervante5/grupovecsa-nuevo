<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Boutique\UpdateBoutiqueOrderStatusRequest;
use App\Models\Boutique\BoutiqueOrder;
use App\Services\Boutique\BoutiqueInventoryService;
use App\Services\Boutique\EnviacomService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BoutiqueAdminOrderController extends Controller
{
    protected BoutiqueInventoryService $inventoryService;
    protected EnviacomService $enviacomService;

    public function __construct(BoutiqueInventoryService $inventoryService, EnviacomService $enviacomService)
    {
        $this->inventoryService = $inventoryService;
        $this->enviacomService = $enviacomService;
    }

    public function search(Request $request)
    {
        try {
            $query = BoutiqueOrder::with(['user', 'payment', 'shipment']);

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('shipping_name', 'like', "%{$search}%");
                });
            }

            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            return ApiResponseHelper::apiSuccess(200, 'Pedidos obtenidos exitosamente', ['orders' => $orders]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los pedidos', $e->getMessage(), 500, 'GET_ADMIN_ORDERS_ERROR');
        }
    }

    public function detail(Request $request)
    {
        try {
            $uuid = $request->input('uuid');

            $order = BoutiqueOrder::where('uuid', $uuid)
                ->with(['user', 'orderItems', 'payment', 'shipment'])
                ->first();

            if (!$order) {
                return ApiResponseHelper::apiError('El pedido no existe', null, 404, 'ORDER_NOT_FOUND');
            }

            return ApiResponseHelper::apiSuccess(200, 'Detalle del pedido obtenido exitosamente', ['order' => $order]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el detalle del pedido', $e->getMessage(), 500, 'GET_ADMIN_ORDER_DETAIL_ERROR');
        }
    }

    public function updateStatus(UpdateBoutiqueOrderStatusRequest $request)
    {
        try {
            $data = $request->validated();

            $order = BoutiqueOrder::findByUuid($data['uuid']);
            if (!$order) {
                return ApiResponseHelper::apiError('El pedido no existe', null, 404, 'ORDER_NOT_FOUND');
            }

            // Validate state transitions
            $validTransitions = [
                'pendiente' => ['pagado', 'cancelado'],
                'pagado' => ['en_preparacion', 'cancelado'],
                'en_preparacion' => ['enviado', 'cancelado'],
                'enviado' => ['entregado'],
                'entregado' => [],
                'cancelado' => [],
            ];

            $currentStatus = $order->status;
            $newStatus = $data['status'];

            if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
                return ApiResponseHelper::apiError(
                    "No se puede cambiar el estado de '{$currentStatus}' a '{$newStatus}'",
                    null,
                    400,
                    'INVALID_STATUS_TRANSITION'
                );
            }

            // If cancelling, restore inventory (incl. variantes)
            if ($newStatus === 'cancelado') {
                $order->load('orderItems.product');
                foreach ($order->orderItems as $orderItem) {
                    if ($orderItem->product) {
                        $this->inventoryService->restoreSaleForOrderItem(
                            $orderItem,
                            'cancelacion',
                            (string) $order->uuid
                        );
                    }
                }
            }

            $order->update(['status' => $newStatus]);

            // Update shipment status if relevant
            $shipmentStatusMap = [
                'en_preparacion' => 'en_preparacion',
                'enviado' => 'enviado',
                'entregado' => 'entregado',
            ];

            if (isset($shipmentStatusMap[$newStatus]) && $order->shipment) {
                $order->shipment->update(['status' => $shipmentStatusMap[$newStatus]]);
            }

            return ApiResponseHelper::apiSuccess(200, 'Estado del pedido actualizado exitosamente', [
                'order' => $order->fresh(['payment', 'shipment']),
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar el estado del pedido', $e->getMessage(), 500, 'UPDATE_ORDER_STATUS_ERROR');
        }
    }

    public function generateLabel(Request $request)
    {
        try {
            $orderUuid = $request->input('uuid');

            $order = BoutiqueOrder::where('uuid', $orderUuid)
                ->with('shipment')
                ->first();

            if (!$order) {
                return ApiResponseHelper::apiError('El pedido no existe', null, 404, 'ORDER_NOT_FOUND');
            }

            if (!$order->shipment) {
                return ApiResponseHelper::apiError('No hay envío asociado al pedido', null, 400, 'NO_SHIPMENT');
            }

            $origin = [
                'name' => env('ENVIACOM_ORIGIN_NAME', ''),
                'street' => env('ENVIACOM_ORIGIN_STREET', ''),
                'city' => env('ENVIACOM_ORIGIN_CITY', ''),
                'state' => env('ENVIACOM_ORIGIN_STATE', ''),
                'postal_code' => env('ENVIACOM_ORIGIN_ZIP', ''),
                'phone' => env('ENVIACOM_ORIGIN_PHONE', ''),
                'country' => env('ENVIACOM_ORIGIN_COUNTRY', 'MX'),
            ];

            $destination = [
                'name' => $order->shipping_name,
                'street' => $order->shipping_address,
                'city' => $order->shipping_city,
                'state' => $order->shipping_state,
                'postal_code' => $order->shipping_zip,
                'phone' => $order->shipping_phone,
                'country' => 'MX',
            ];

            $packages = [
                [
                    'content' => 'Productos Boutique VECSA - ' . $order->order_number,
                    'amount' => 1,
                    'type' => 'box',
                    'weight' => 1,
                    'insurance' => 0,
                    'declaredValue' => (float) $order->total,
                    'weightUnit' => 'KG',
                    'lengthUnit' => 'CM',
                    'dimensions' => ['length' => 30, 'width' => 30, 'height' => 20],
                ],
            ];

            $carrier = $order->shipment->carrier_name ?? 'fedex';

            $result = $this->enviacomService->createShipment($origin, $destination, $packages, $carrier);

            $order->shipment->update([
                'tracking_number' => $result['tracking_number'],
                'envia_label_url' => $result['label_url'],
                'envia_shipment_id' => $result['shipment_id'],
                'status' => 'enviado',
            ]);

            $order->update(['status' => 'enviado']);

            return ApiResponseHelper::apiSuccess(200, 'Guía de envío generada exitosamente', [
                'shipment' => $order->shipment->fresh(),
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al generar la guía de envío', $e->getMessage(), 500, 'GENERATE_LABEL_ERROR');
        }
    }

    public function metrics(Request $request)
    {
        try {
            $query = BoutiqueOrder::query();

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            $totalOrders = (clone $query)->count();
            $pendingOrders = (clone $query)->where('status', 'pendiente')->count();
            $revenue = (clone $query)->whereIn('status', ['pagado', 'en_preparacion', 'enviado', 'entregado'])->sum('total');

            return ApiResponseHelper::apiSuccess(200, 'Métricas obtenidas exitosamente', [
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'revenue' => round($revenue, 2),
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener métricas', $e->getMessage(), 500, 'GET_METRICS_ERROR');
        }
    }
}
