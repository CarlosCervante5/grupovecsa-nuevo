<?php

namespace App\Http\Controllers\Dealerships;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;

use App\Models\Dealership;

class DealershipController extends Controller
{
    public function search()
    {
        try {

            $dealerships = Dealership::all();

            return ApiResponseHelper::authSuccess(200, 'Sucursales encontradas', $dealerships);

        } catch (\Exception $e) {

            return ApiResponseHelper::apiError('Error al obtener las sucursales', $e->getMessage(), 500, 'GET_DEALERSHIPS_ERROR');
        }
    }

    public function users(\Illuminate\Http\Request $request)
    {
        try {
            $data = $request->validate([
                'dealership_id' => 'required|integer|exists:' . env('DB_TABLE_PREFIX', '') . 'dealerships,id',
            ]);

            $dealership = Dealership::find($data['dealership_id']);
            if (!$dealership) {
                return ApiResponseHelper::apiError('Sucursal no encontrada', null, 404, 'DEALERSHIP_NOT_FOUND');
            }

            $users = $dealership->users()->with('userProfile')->paginate(15);

            return ApiResponseHelper::apiSuccess(200, 'Usuarios de la sucursal obtenidos', ['users' => $users]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener usuarios de la sucursal', $e->getMessage(), 500, 'GET_DEALERSHIP_USERS_ERROR');
        }
    }
}
