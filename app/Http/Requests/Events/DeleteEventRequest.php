<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

class DeleteEventRequest extends FormRequest
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