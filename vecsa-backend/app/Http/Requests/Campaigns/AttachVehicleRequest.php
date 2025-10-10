<?php

namespace App\Http\Requests\Campaigns;

use Illuminate\Foundation\Http\FormRequest;

class AttachVehicleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'vehicle_uuid' => 'required|uuid',
            'campaing_uuids' => 'required|array',
            'campaing_uuids.*' => 'required|uuid',
        ];
    }
}