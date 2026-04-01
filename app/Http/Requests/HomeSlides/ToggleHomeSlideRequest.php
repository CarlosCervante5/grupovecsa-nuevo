<?php

namespace App\Http\Requests\HomeSlides;

use Illuminate\Foundation\Http\FormRequest;

class ToggleHomeSlideRequest extends FormRequest
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
