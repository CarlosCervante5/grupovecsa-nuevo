<?php

namespace App\Http\Requests\Valuations;

use Illuminate\Foundation\Http\FormRequest;

class DetailRepairsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'valuation_uuid' => [
                'required',
                'string',
                'uuid',
            ],
        ];
    }
}