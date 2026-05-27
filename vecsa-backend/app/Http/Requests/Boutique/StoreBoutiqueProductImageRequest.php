<?php

namespace App\Http\Requests\Boutique;

use Illuminate\Foundation\Http\FormRequest;

class StoreBoutiqueProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_uuid' => 'required|string',
            'image' => 'required|file|uploadable_image|max:5120',
        ];
    }
}
