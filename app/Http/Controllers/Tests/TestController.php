<?php

namespace App\Http\Controllers\Tests;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Strega\Opportunities\UpdateOpportunityRequest;


class TestController extends Controller
{
   
    public function timeZone()
    {
        try {

            dd(config('app.timezone'), date_default_timezone_get());

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar la oportunidad', $e->getMessage(), 500, 'UPDATE_OPPORTUNITY_ERROR');
        }
    }
}
