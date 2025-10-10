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
}
