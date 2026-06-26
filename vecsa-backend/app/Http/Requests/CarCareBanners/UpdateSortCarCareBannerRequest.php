<?php

namespace App\Http\Requests\CarCareBanners;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSortCarCareBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image_order' => 'required|array',
            'image_order.*.uuid' => 'required|string',
            'image_order.*.sort_id' => 'required|integer',
        ];
    }
}
