<?php

namespace App\Http\Requests\Vehicles;

use App\Rules\ActiveVehicleExists;
use App\Rules\DeletedVehicleExists;
use Illuminate\Foundation\Http\FormRequest;

class RestoreVehicleRequest extends FormRequest
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
                new DeletedVehicleExists,
            ]
        ];
    }

}