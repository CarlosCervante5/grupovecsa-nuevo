<?php

namespace App\Http\Requests\HomeTestimonials;

use Illuminate\Foundation\Http\FormRequest;

class ToggleHomeTestimonialRequest extends FormRequest
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
                'uuid'
            ]
        ];
    }
}
