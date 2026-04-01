<?php

namespace App\Http\Controllers\StoreManagement;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\CustomerCoupon;
use Illuminate\Http\Request;

class StoreCouponController extends Controller
{
    /**
     * Paginated coupon list.
     */
    public function search(Request $request)
    {
        try {
            $query = CustomerCoupon::query();

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('discount_type')) {
                $query->where('discount_type', $request->input('discount_type'));
            }

            $coupons = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            return ApiResponseHelper::apiSuccess(200, 'Cupones obtenidos exitosamente', ['coupons' => $coupons]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los cupones', $e->getMessage(), 500, 'SEARCH_COUPONS_ERROR');
        }
    }

    /**
     * Create a new coupon.
     * Validates unique code (case-insensitive), alphanumeric with dashes, 4-20 chars.
     * If percentage → amount <= 100. Stores code in UPPER, usage_count=0.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'code' => ['required', 'string', 'min:4', 'max:20', 'regex:/^[A-Za-z0-9\-]+$/'],
                'amount' => 'required|numeric|gt:0',
                'discount_type' => 'required|string|in:percentage,fixed',
                'description' => 'nullable|string',
                'usage_limit' => 'nullable|integer|min:0',
                'minimum_amount' => 'nullable|numeric|min:0',
                'maximum_amount' => 'nullable|numeric|min:0',
                'individual_use' => 'nullable|boolean',
            ]);

            $code = strtoupper($request->input('code'));

            // Validate percentage amount <= 100
            if ($request->input('discount_type') === 'percentage' && $request->input('amount') > 100) {
                return ApiResponseHelper::apiError(
                    'El monto de un cupón de porcentaje no puede ser mayor a 100',
                    null,
                    422,
                    'INVALID_PERCENTAGE_AMOUNT'
                );
            }

            // Validate max >= min
            if ($request->filled('minimum_amount') && $request->filled('maximum_amount')) {
                if ($request->input('maximum_amount') < $request->input('minimum_amount')) {
                    return ApiResponseHelper::apiError(
                        'El monto máximo debe ser mayor o igual al monto mínimo',
                        null,
                        422,
                        'INVALID_AMOUNT_RANGE'
                    );
                }
            }

            // Check unique code (case-insensitive)
            $existing = CustomerCoupon::whereRaw('UPPER(code) = ?', [$code])->first();
            if ($existing) {
                return ApiResponseHelper::apiError(
                    'El código de cupón ya existe',
                    null,
                    422,
                    'DUPLICATE_COUPON_CODE'
                );
            }

            $coupon = CustomerCoupon::create([
                'code' => $code,
                'amount' => $request->input('amount'),
                'discount_type' => $request->input('discount_type'),
                'description' => $request->input('description'),
                'usage_count' => 0,
                'usage_limit' => $request->input('usage_limit'),
                'minimum_amount' => $request->input('minimum_amount'),
                'maximum_amount' => $request->input('maximum_amount'),
                'individual_use' => $request->input('individual_use', false),
                'source' => 'store_management',
            ]);

            return ApiResponseHelper::apiSuccess(201, 'Cupón creado exitosamente', ['coupon' => $coupon]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear el cupón', $e->getMessage(), 500, 'CREATE_COUPON_ERROR');
        }
    }

    /**
     * Update coupon fields.
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'uuid' => 'required|string',
            ]);

            $coupon = CustomerCoupon::where('uuid', $request->input('uuid'))->first();

            if (!$coupon) {
                return ApiResponseHelper::apiError('El cupón no existe', null, 404, 'COUPON_NOT_FOUND');
            }

            $updateData = $request->only([
                'code', 'amount', 'discount_type', 'description',
                'usage_limit', 'minimum_amount', 'maximum_amount', 'individual_use',
            ]);

            // If code is being updated, store in UPPER and check uniqueness
            if (isset($updateData['code'])) {
                $updateData['code'] = strtoupper($updateData['code']);
                $existing = CustomerCoupon::whereRaw('UPPER(code) = ?', [$updateData['code']])
                    ->where('id', '!=', $coupon->id)
                    ->first();
                if ($existing) {
                    return ApiResponseHelper::apiError(
                        'El código de cupón ya existe',
                        null,
                        422,
                        'DUPLICATE_COUPON_CODE'
                    );
                }
            }

            // Validate percentage amount
            $discountType = $updateData['discount_type'] ?? $coupon->discount_type;
            $amount = $updateData['amount'] ?? $coupon->amount;
            if ($discountType === 'percentage' && $amount > 100) {
                return ApiResponseHelper::apiError(
                    'El monto de un cupón de porcentaje no puede ser mayor a 100',
                    null,
                    422,
                    'INVALID_PERCENTAGE_AMOUNT'
                );
            }

            $coupon->update($updateData);

            return ApiResponseHelper::apiSuccess(200, 'Cupón actualizado exitosamente', ['coupon' => $coupon->fresh()]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar el cupón', $e->getMessage(), 500, 'UPDATE_COUPON_ERROR');
        }
    }

    /**
     * Soft delete a coupon.
     */
    public function delete(Request $request)
    {
        try {
            $uuid = $request->input('uuid');

            $coupon = CustomerCoupon::where('uuid', $uuid)->first();

            if (!$coupon) {
                return ApiResponseHelper::apiError('El cupón no existe', null, 404, 'COUPON_NOT_FOUND');
            }

            $coupon->delete();

            return ApiResponseHelper::apiSuccess(200, 'Cupón eliminado exitosamente', null);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar el cupón', $e->getMessage(), 500, 'DELETE_COUPON_ERROR');
        }
    }
}
