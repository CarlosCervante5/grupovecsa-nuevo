<?php

namespace App\Http\Requests\Vehicles\VehicleImages;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleImageStorageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'vehicle_uuid'=> [
                'required',
                'string',
                'uuid',
            ]
        ];
    }

}