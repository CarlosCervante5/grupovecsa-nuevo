<?php

namespace App\Http\Requests\CarCareBanners;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarCareBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'subtitle' => 'nullable|string',
            'disclaimer' => 'nullable|string',
            'desktop_image' => 'nullable|file|uploadable_image|max:5120',
            'mobile_image' => 'nullable|file|uploadable_image|max:5120',
        ];
    }
}
