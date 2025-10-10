<?php

namespace App\Http\Requests\Vehicles;

use App\Rules\ActiveVehicleExists;
use Illuminate\Foundation\Http\FormRequest;

class DeleteVehicleRequest extends FormRequest
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
                new ActiveVehicleExists,
            ]
        ];
    }

}