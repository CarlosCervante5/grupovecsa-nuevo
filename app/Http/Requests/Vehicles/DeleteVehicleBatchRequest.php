<?php

namespace App\Http\Requests\Vehicles;

use App\Rules\ActiveVehicleExists;
use Illuminate\Foundation\Http\FormRequest;

class DeleteVehicleBatchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'uuids' => 'required|array',
            'uuids.*' => [
                'required',
                'string',
                'uuid',
                new ActiveVehicleExists,
            ]
        ];
    }

}