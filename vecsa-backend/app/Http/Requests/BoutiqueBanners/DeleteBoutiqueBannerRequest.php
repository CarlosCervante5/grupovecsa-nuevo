<?php

namespace App\Http\Requests\BoutiqueBanners;

use Illuminate\Foundation\Http\FormRequest;

class DeleteBoutiqueBannerRequest extends FormRequest
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
