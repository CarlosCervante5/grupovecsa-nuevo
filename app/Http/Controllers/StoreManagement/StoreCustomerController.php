<?php

namespace App\Http\Controllers\StoreManagement;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Boutique\BoutiqueOrder;
use App\Models\Customer;
use App\Models\CustomerCoupon;
use App\Models\CustomerReward;
use App\Models\RewardPoint;
use Illuminate\Http\Request;

class StoreCustomerController extends Controller
{
    /**
     * Paginated customer list with search by name/email.
     */
    public function search(Request $request)
    {
        try {
            $query = Customer::query();

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email_1', 'like', "%{$search}%");
                });
            }

            $customers = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            return ApiResponseHelper::apiSuccess(200, 'Clientes obtenidos exitosamente', ['customers' => $customers]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los clientes', $e->getMessage(), 500, 'SEARCH_CUSTOMERS_ERROR');
        }
    }

    /**
     * Customer detail with total_points, orders, rewards, coupons.
     */
    public function detail(Request $request)
    {
        try {
            $uuid = $request->input('uuid');

            $customer = Customer::where('uuid', $uuid)->first();

            if (!$customer) {
                return ApiResponseHelper::apiError('El cliente no existe', null, 404, 'CUSTOMER_NOT_FOUND');
            }

            // Calculate total points (sum of earned_points not redeemed)
            $customerRewardIds = CustomerReward::where('customer_id', $customer->id)->pluck('id');
            $totalPoints = RewardPoint::whereIn('customer_reward_id', $customerRewardIds)
                ->where('redeemed', false)
                ->sum('earned_points');

            // Get orders
            $orders = BoutiqueOrder::where('user_id', $customer->user_id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Get rewards
            $rewards = CustomerReward::where('customer_id', $customer->id)
                ->with('reward')
                ->get();

            // Get coupons
            $coupons = CustomerCoupon::where('customer_id', $customer->id)->get();

            return ApiResponseHelper::apiSuccess(200, 'Detalle del cliente obtenido exitosamente', [
                'customer' => $customer,
                'total_points' => round($totalPoints, 2),
                'orders' => $orders,
                'rewards' => $rewards,
                'coupons' => $coupons,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el detalle del cliente', $e->getMessage(), 500, 'CUSTOMER_DETAIL_ERROR');
        }
    }

    /**
     * Customer order history.
     */
    public function customerOrders(Request $request)
    {
        try {
            $uuid = $request->input('uuid');

            $customer = Customer::where('uuid', $uuid)->first();

            if (!$customer) {
                return ApiResponseHelper::apiError('El cliente no existe', null, 404, 'CUSTOMER_NOT_FOUND');
            }

            $orders = BoutiqueOrder::where('user_id', $customer->user_id)
                ->with(['orderItems', 'payment', 'shipment'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            return ApiResponseHelper::apiSuccess(200, 'Pedidos del cliente obtenidos exitosamente', ['orders' => $orders]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los pedidos del cliente', $e->getMessage(), 500, 'CUSTOMER_ORDERS_ERROR');
        }
    }
}
