<?php

namespace App\Http\Requests\HomeSlides;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSortHomeSlideRequest extends FormRequest
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
            'image_order.*.sort_id' => 'required|integer',
        ];
    }
}
