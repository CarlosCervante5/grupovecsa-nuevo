<?php

namespace App\Http\Requests\Vehicles;

use Illuminate\Foundation\Http\FormRequest;

class RandomSearchVehiclesRequest extends FormRequest
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
            'status' => 'sometimes|array',
            'status.*' => 'sometimes|string|min:1',
            'prices' => 'sometimes|array',
            'prices.*' => 'sometimes|numeric|min:0',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'relationship_names' => $this->relationship_names ?? ['brand', 'line', 'model', 'version', 'body', 'dealership', 'firstImage', 'campaigns.promotions'],
            'status' => $this->status ?? ['active'],
            'prices' => $this->prices ?? [],
        ]);
    }
}