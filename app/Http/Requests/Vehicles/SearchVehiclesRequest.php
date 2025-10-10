<?php

namespace App\Http\Requests\Vehicles;

use Illuminate\Foundation\Http\FormRequest;

class SearchVehiclesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'relationship_names' => 'sometimes|nullable',
            'status' => 'sometimes|nullable',
            'categories' => 'sometimes|nullable',
            'brand_names' => 'sometimes|nullable',
            'line_names' => 'sometimes|nullable',
            'model_names' => 'sometimes|nullable',
            'body_names' => 'sometimes|nullable',
            'version_names' => 'sometimes|nullable',
            'location_names' => 'sometimes|nullable',
            'transmission_names' => 'sometimes|nullable',
            'exterior_colors' => 'sometimes|nullable',
            'interior_colors' => 'sometimes|nullable',
            'years' => 'sometimes|nullable',
            'prices' => 'sometimes|nullable',
            'keyword' => 'sometimes|string|nullable',
            'filters' => 'sometimes|boolean',
            'has_images' => 'sometimes|boolean',
            'order_by' => 'sometimes|string|nullable',
            'paginate' => 'sometimes|integer|min:1',
        ];
    }

    protected function prepareForValidation()
    {
        $stringToArray = function ($string) {
            return $string ? explode(',', $string) : [];
        };

        $this->merge([
            'relationship_names' => $this->has('relationship_names')
                ? $stringToArray($this->input('relationship_names'))
                : ['brand', 'line', 'model', 'version', 'body', 'dealership', 'specification', 'firstImage', 'campaigns.promotions'],

            'status' => ($this->has('status') && !empty($this->input('status')))
                ? $stringToArray($this->input('status'))
                : ['active'],

            'categories' => $this->has('categories')
                ? $stringToArray($this->input('categories'))
                : [],

            'brand_names' => $this->has('brand_names')
                ? $stringToArray($this->input('brand_names'))
                : [],

            'line_names' => $this->has('line_names')
                ? $stringToArray($this->input('line_names'))
                : [],

            'model_names' => $this->has('model_names')
                ? $stringToArray($this->input('model_names'))
                : [],

            'body_names' => $this->has('body_names')
                ? $stringToArray($this->input('body_names'))
                : [],

            'version_names' => $this->has('version_names')
                ? $stringToArray($this->input('version_names'))
                : [],

            'location_names' => $this->has('location_names')
                ? $stringToArray($this->input('location_names'))
                : [],

            'transmission_names' => $this->has('transmission_names')
                ? $stringToArray($this->input('transmission_names'))
                : [],

            'interior_colors' => $this->has('interior_colors')
                ? $stringToArray($this->input('interior_colors'))
                : [],
            
            'exterior_colors' => $this->has('exterior_colors')
                ? $stringToArray($this->input('exterior_colors'))
                : [],

            'years' => $this->has('years')
                ? array_map('intval', $stringToArray($this->input('years')))
                : [],

            'prices' => $this->has('prices')
                ? array_map('intval', $stringToArray($this->input('prices')))
                : [],

            'keyword' => $this->input('keyword', ''),

            'filters' => $this->has('filters') ? $this->boolean('filters') : false,

            'has_images' => $this->has('has_images') ? $this->boolean('has_images') : true,

            'order_by' => $this->input('order_by', ''),

            'paginate' => $this->input('paginate', 15),
        ]);
    }
}