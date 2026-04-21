<?php

namespace App\Http\Requests\Boutique;

use Illuminate\Foundation\Http\FormRequest;

class StoreBoutiqueProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sku' => 'required|string|max:100',
            'category_uuid' => 'required|string',
            'dealership_id' => 'nullable|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'active' => 'nullable|boolean',
        ];
    }
}
