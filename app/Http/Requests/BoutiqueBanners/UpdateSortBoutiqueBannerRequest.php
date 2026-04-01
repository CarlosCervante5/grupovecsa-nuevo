<?php

namespace App\Http\Requests\BoutiqueBanners;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSortBoutiqueBannerRequest extends FormRequest
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
