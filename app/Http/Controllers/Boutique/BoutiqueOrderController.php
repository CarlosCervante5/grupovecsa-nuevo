<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Boutique\BoutiqueOrder;
use App\Support\BoutiqueTransferBankDetails;
use Illuminate\Http\Request;

class BoutiqueOrderController extends Controller
{
    public function search(Request $request)
    {
        try {
            $user = $request->user();

            $orders = BoutiqueOrder::where('user_id', $user->id)
                ->withCount('orderItems')
                ->orderBy('created_at', 'desc')
                ->get();

            return ApiResponseHelper::apiSuccess(200, 'Pedidos obtenidos exitosamente', ['orders' => $orders]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los pedidos', $e->getMessage(), 500, 'GET_ORDERS_ERROR');
        }
    }

    public function detail(Request $request)
    {
        try {
            $uuid = $request->input('uuid');
            $user = $request->user();

            $order = BoutiqueOrder::where('uuid', $uuid)
                ->where('user_id', $user->id)
                ->with(['orderItems', 'payment', 'shipment'])
                ->first();

            if (!$order) {
                return ApiResponseHelper::apiError('El pedido no existe', null, 404, 'ORDER_NOT_FOUND');
            }

            $payload = ['order' => $order];
            if ($order->status === 'pendiente' && $order->payment?->method === 'transferencia') {
                $payload['transfer_bank'] = BoutiqueTransferBankDetails::publicPayload();
            }

            return ApiResponseHelper::apiSuccess(200, 'Detalle del pedido obtenido exitosamente', $payload);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el detalle del pedido', $e->getMessage(), 500, 'GET_ORDER_DETAIL_ERROR');
        }
    }
}
