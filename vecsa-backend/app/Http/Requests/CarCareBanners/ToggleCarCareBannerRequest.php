<?php

namespace App\Http\Requests\CarCareBanners;

use Illuminate\Foundation\Http\FormRequest;

class ToggleCarCareBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuid' => 'required|string',
        ];
    }
}
