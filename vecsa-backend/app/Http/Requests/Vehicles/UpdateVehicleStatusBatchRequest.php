<?php

namespace App\Http\Requests\Vehicles;

use App\Rules\ActiveVehicleExists;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleStatusBatchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'page_status' => 'required|string',
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