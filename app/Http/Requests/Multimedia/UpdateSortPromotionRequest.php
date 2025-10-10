<?php

namespace App\Http\Requests\Multimedia;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSortMultimediaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'multimedia_order' => 'required|array',
            'multimedia_order.*.uuid' => 'required|uuid',
            'multimedia_order.*.sort_id' => 'required|integer|min:1',
        ];
    }
}