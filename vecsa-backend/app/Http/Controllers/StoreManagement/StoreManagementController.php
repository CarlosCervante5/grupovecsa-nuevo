<?php

namespace App\Http\Controllers\StoreManagement;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Boutique\BoutiqueOrder;
use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueShipment;
use App\Models\Customer;
use App\Services\Boutique\BoutiqueInventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StoreManagementController extends Controller
{
    protected BoutiqueInventoryService $inventoryService;

    public function __construct(BoutiqueInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Dashboard metrics: total_orders, revenue, pending_orders, total_customers,
     * total_products, orders_by_month (last 6 months), orders_by_status.
     */
    public function dashboard(Request $request)
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

            $paidStatuses = ['pagado', 'en_preparacion', 'enviado', 'entregado'];
            $revenue = (clone $query)->whereIn('status', $paidStatuses)->sum('total');

            $totalCustomers = Customer::count();
            $totalProducts = BoutiqueProduct::where('active', true)->count();

            // Orders by month — last 6 months (SQLite compatible with whereBetween)
            $ordersByMonth = [];
            for ($i = 5; $i >= 0; $i--) {
                $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
                $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
                $monthKey = $monthStart->format('Y-m');
                $monthCount = BoutiqueOrder::whereBetween('created_at', [$monthStart, $monthEnd])->count();
                $ordersByMonth[] = ['month' => $monthKey, 'count' => $monthCount];
            }

            // Orders by status
            $statuses = ['pendiente', 'pagado', 'en_preparacion', 'enviado', 'entregado', 'cancelado'];
            $ordersByStatus = [];
            foreach ($statuses as $status) {
                $statusCount = (clone $query)->where('status', $status)->count();
                if ($statusCount > 0) {
                    $ordersByStatus[] = ['status' => $status, 'count' => $statusCount];
                }
            }

            return ApiResponseHelper::apiSuccess(200, 'Métricas del dashboard obtenidas exitosamente', [
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'revenue' => round($revenue, 2),
                'total_customers' => $totalCustomers,
                'total_products' => $totalProducts,
                'orders_by_month' => $ordersByMonth,
                'orders_by_status' => $ordersByStatus,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener métricas del dashboard', $e->getMessage(), 500, 'DASHBOARD_METRICS_ERROR');
        }
    }

    /**
     * Search orders with pagination, filters by status, date range, and search term.
     */
    public function searchOrders(Request $request)
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
            return ApiResponseHelper::apiError('Error al obtener los pedidos', $e->getMessage(), 500, 'SEARCH_ORDERS_ERROR');
        }
    }

    /**
     * Get order detail by UUID with all relations.
     */
    public function orderDetail(Request $request)
    {
        try {
            $uuid = $request->input('uuid');

            $order = BoutiqueOrder::where('uuid', $uuid)
                ->with(['user', 'orderItems.product', 'payment', 'shipment'])
                ->first();

            if (!$order) {
                return ApiResponseHelper::apiError('El pedido no existe', null, 404, 'ORDER_NOT_FOUND');
            }

            return ApiResponseHelper::apiSuccess(200, 'Detalle del pedido obtenido exitosamente', ['order' => $order]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el detalle del pedido', $e->getMessage(), 500, 'ORDER_DETAIL_ERROR');
        }
    }

    /**
     * Update order status with valid state transitions.
     * Restores inventory on cancellation and syncs shipment status.
     */
    public function updateOrderStatus(Request $request)
    {
        try {
            $request->validate([
                'uuid' => 'required|string',
                'status' => 'required|string',
            ]);

            $order = BoutiqueOrder::findByUuid($request->input('uuid'));
            if (!$order) {
                return ApiResponseHelper::apiError('El pedido no existe', null, 404, 'ORDER_NOT_FOUND');
            }

            $validTransitions = [
                'pendiente' => ['pagado', 'cancelado'],
                'pagado' => ['en_preparacion', 'cancelado'],
                'en_preparacion' => ['enviado', 'cancelado'],
                'enviado' => ['entregado'],
                'entregado' => [],
                'cancelado' => [],
            ];

            $currentStatus = $order->status;
            $newStatus = $request->input('status');

            if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
                return ApiResponseHelper::apiError(
                    "No se puede cambiar el estado de '{$currentStatus}' a '{$newStatus}'",
                    null,
                    400,
                    'INVALID_STATUS_TRANSITION'
                );
            }

            // Restore inventory on cancellation (incl. variantes)
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

            // Sync shipment status
            $shipmentStatusMap = [
                'en_preparacion' => 'en_preparacion',
                'enviado' => 'enviado',
                'entregado' => 'entregado',
            ];

            if (isset($shipmentStatusMap[$newStatus]) && $order->shipment) {
                $order->shipment->update(['status' => $shipmentStatusMap[$newStatus]]);
            }

            return ApiResponseHelper::apiSuccess(200, 'Estado del pedido actualizado exitosamente', [
                'order' => $order->fresh(['user', 'payment', 'shipment']),
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar el estado del pedido', $e->getMessage(), 500, 'UPDATE_ORDER_STATUS_ERROR');
        }
    }

    /**
     * Generate shipping label — placeholder for EnviacomService integration.
     */
    public function generateLabel(Request $request)
    {
        try {
            $uuid = $request->input('uuid');

            $order = BoutiqueOrder::where('uuid', $uuid)->with('shipment')->first();

            if (!$order) {
                return ApiResponseHelper::apiError('El pedido no existe', null, 404, 'ORDER_NOT_FOUND');
            }

            // Placeholder — EnviacomService integration will be added later
            return ApiResponseHelper::apiSuccess(200, 'Guía de envío generada exitosamente', [
                'message' => 'Integración con EnviacomService pendiente',
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al generar la guía de envío', $e->getMessage(), 500, 'GENERATE_LABEL_ERROR');
        }
    }

    /**
     * Search shipments with pagination and filters.
     */
    public function searchShipments(Request $request)
    {
        try {
            $query = BoutiqueShipment::with(['order']);

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('carrier')) {
                $query->where('carrier_name', $request->input('carrier'));
            }

            $shipments = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            return ApiResponseHelper::apiSuccess(200, 'Envíos obtenidos exitosamente', ['shipments' => $shipments]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los envíos', $e->getMessage(), 500, 'SEARCH_SHIPMENTS_ERROR');
        }
    }
}
