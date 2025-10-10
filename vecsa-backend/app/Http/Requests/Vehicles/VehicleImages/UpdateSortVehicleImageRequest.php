<?php

namespace App\Http\Requests\Vehicles\VehicleImages;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSortVehicleImageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'image_order' => 'required|array',
            'image_order.*.uuid' => 'required|uuid',
            'image_order.*.sort_id' => 'required|integer|min:1',
        ];
    }

}