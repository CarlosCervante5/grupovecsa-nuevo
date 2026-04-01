<?php

namespace App\Http\Requests\Boutique;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_uuid' => 'required|string',
            'new_stock' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
        ];
    }
}
