<?php

namespace App\Http\Requests\Vehicles\VehicleImages;

use Illuminate\Foundation\Http\FormRequest;

class DeleteVehicleImageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'uuid' => [
                'required',
                'string',
                'uuid',
            ]
        ];
    }

}