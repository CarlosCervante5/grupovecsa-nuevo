<?php

namespace App\Http\Requests\Multimedia;

use Illuminate\Foundation\Http\FormRequest;

class DeleteMultimediaRequest extends FormRequest
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