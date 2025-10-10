<?php

namespace App\Http\Requests\Vehicles;

use App\Rules\ActiveVehicleExists;
use Illuminate\Foundation\Http\FormRequest;

class DetailVehicleRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Puedes ajustar la lógica de autorización según tus necesidades
    }

    public function rules()
    {
        return [
            'relationship_names' => 'sometimes|array',
            'relationship_names.*' => 'sometimes|in:brand,line,model,version,body,dealership,specification,images,firstImage,campaigns.promotions',
            'uuid' => [
                'required',
                'string',
                'uuid',
                new ActiveVehicleExists,
            ]
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'relationship_names' => $this->relationship_names ?? ['brand', 'line', 'model', 'version', 'body', 'dealership', 'specification', 'images', 'campaigns.promotions'],
        ]);
    }

}