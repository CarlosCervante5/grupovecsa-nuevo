<?php

namespace App\Http\Requests\HomeTestimonials;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSortHomeTestimonialRequest extends FormRequest
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
