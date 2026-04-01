<?php

namespace App\Http\Controllers\StoreManagement;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerReward;
use App\Models\RewardPoint;
use Illuminate\Http\Request;

class StorePointsController extends Controller
{
    /**
     * List customers with points balance.
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

            $customers = $query->with('customerRewards')
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            // Append total_points to each customer
            $customers->getCollection()->transform(function ($customer) {
                $rewardIds = $customer->customerRewards->pluck('id');
                $customer->total_points = round(
                    RewardPoint::whereIn('customer_reward_id', $rewardIds)->sum('earned_points'),
                    2
                );
                return $customer;
            });

            return ApiResponseHelper::apiSuccess(200, 'Clientes con puntos obtenidos exitosamente', ['customers' => $customers]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener clientes con puntos', $e->getMessage(), 500, 'SEARCH_POINTS_ERROR');
        }
    }

    /**
     * Adjust customer points manually.
     * Validates points > 0, reason >= 5 chars, type in [add, subtract].
     * If subtract and balance < points → 400 INSUFFICIENT_BALANCE.
     */
    public function adjust(Request $request)
    {
        try {
            $request->validate([
                'customer_reward_uuid' => 'required|string',
                'points' => 'required|numeric|gt:0',
                'reason' => 'required|string|min:5',
                'type' => 'required|string|in:add,subtract',
            ]);

            $customerReward = CustomerReward::where('uuid', $request->input('customer_reward_uuid'))->first();

            if (!$customerReward) {
                return ApiResponseHelper::apiError('Registro de reward no encontrado', null, 404, 'CUSTOMER_REWARD_NOT_FOUND');
            }

            // Calculate current balance
            $currentBalance = RewardPoint::where('customer_reward_id', $customerReward->id)
                ->sum('earned_points');

            $type = $request->input('type');
            $points = $request->input('points');

            if ($type === 'subtract' && $currentBalance < $points) {
                return ApiResponseHelper::apiError(
                    'Balance insuficiente para restar puntos',
                    null,
                    400,
                    'INSUFFICIENT_BALANCE'
                );
            }

            $earnedPoints = $type === 'subtract' ? -$points : $points;

            $newPoint = RewardPoint::create([
                'name' => 'ajuste_manual',
                'earned_points' => $earnedPoints,
                'detail' => $request->input('reason'),
                'customer_reward_id' => $customerReward->id,
                'redeemed' => false,
            ]);

            $newBalance = $currentBalance + $earnedPoints;

            return ApiResponseHelper::apiSuccess(200, 'Ajuste de puntos realizado exitosamente', [
                'new_balance' => round($newBalance, 2),
                'point' => $newPoint,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al ajustar puntos', $e->getMessage(), 500, 'ADJUST_POINTS_ERROR');
        }
    }

    /**
     * Points movement history for a customer reward.
     */
    public function customerBalance(Request $request)
    {
        try {
            $uuid = $request->input('customer_reward_uuid');

            $customerReward = CustomerReward::where('uuid', $uuid)->first();

            if (!$customerReward) {
                return ApiResponseHelper::apiError('Registro de reward no encontrado', null, 404, 'CUSTOMER_REWARD_NOT_FOUND');
            }

            $points = RewardPoint::where('customer_reward_id', $customerReward->id)
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            $balance = RewardPoint::where('customer_reward_id', $customerReward->id)
                ->sum('earned_points');

            return ApiResponseHelper::apiSuccess(200, 'Historial de puntos obtenido exitosamente', [
                'points' => $points,
                'balance' => round($balance, 2),
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener historial de puntos', $e->getMessage(), 500, 'CUSTOMER_BALANCE_ERROR');
        }
    }

    /**
     * Paginated list of redemptions with status filter.
     */
    public function searchRedemptions(Request $request)
    {
        try {
            $query = RewardPoint::where('redeemed', true)
                ->with(['customerReward.customer', 'customerReward.reward']);

            if ($request->filled('status')) {
                $query->where('name', $request->input('status'));
            }

            $redemptions = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            return ApiResponseHelper::apiSuccess(200, 'Redenciones obtenidas exitosamente', ['redemptions' => $redemptions]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener redenciones', $e->getMessage(), 500, 'SEARCH_REDEMPTIONS_ERROR');
        }
    }

    /**
     * Approve or reject a redemption.
     */
    public function updateRedemptionStatus(Request $request)
    {
        try {
            $request->validate([
                'uuid' => 'required|string',
                'status' => 'required|string|in:aprobada,rechazada',
            ]);

            $point = RewardPoint::where('uuid', $request->input('uuid'))->first();

            if (!$point) {
                return ApiResponseHelper::apiError('Redención no encontrada', null, 404, 'REDEMPTION_NOT_FOUND');
            }

            $point->update(['name' => $request->input('status')]);

            return ApiResponseHelper::apiSuccess(200, 'Estado de redención actualizado exitosamente', [
                'redemption' => $point->fresh(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar estado de redención', $e->getMessage(), 500, 'UPDATE_REDEMPTION_STATUS_ERROR');
        }
    }
}
