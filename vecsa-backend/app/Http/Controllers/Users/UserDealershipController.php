<?php

namespace App\Http\Controllers\Users;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserDealershipController extends Controller
{
    public function assignDealerships(Request $request)
    {
        try {
            $prefix = env('DB_TABLE_PREFIX', '');

            $data = $request->validate([
                'user_uuid' => 'required|string|exists:users,uuid',
                'dealership_ids' => 'present|array',
                'dealership_ids.*' => 'integer|exists:' . $prefix . 'dealerships,id',
            ]);

            $user = User::findByUuid($data['user_uuid']);
            if (!$user) {
                return ApiResponseHelper::apiError('Usuario no encontrado', null, 404, 'USER_NOT_FOUND');
            }

            $user->dealerships()->sync($data['dealership_ids']);

            return ApiResponseHelper::apiSuccess(200, 'Sucursales asignadas exitosamente', [
                'dealerships' => $user->dealerships()->get(['id', 'name']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al asignar sucursales', $e->getMessage(), 500, 'ASSIGN_DEALERSHIPS_ERROR');
        }
    }

    public function getUserDealerships(Request $request)
    {
        try {
            $data = $request->validate([
                'user_uuid' => 'required|string|exists:users,uuid',
            ]);

            $user = User::findByUuid($data['user_uuid']);
            if (!$user) {
                return ApiResponseHelper::apiError('Usuario no encontrado', null, 404, 'USER_NOT_FOUND');
            }

            return ApiResponseHelper::apiSuccess(200, 'Sucursales del usuario obtenidas', [
                'dealerships' => $user->dealerships()->get(['id', 'name']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener sucursales', $e->getMessage(), 500, 'GET_USER_DEALERSHIPS_ERROR');
        }
    }
}
