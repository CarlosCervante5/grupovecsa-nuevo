<?php

namespace App\Http\Requests\Promotions;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSortPromotionRequest extends FormRequest
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
            'image_order.*.sort_id' => 'required|integer|min:1',
        ];
    }
}