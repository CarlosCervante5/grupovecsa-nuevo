<?php

namespace App\Http\Requests\SpareParts;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSparePartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            "price_original" => 'required|numeric|min:0.5|max:99999999.99',
            "delivery_original" => 'required|max:255|string',
            "supplier_original" => 'required|max:255|string',
            "price_generic" => 'nullable|numeric|min:0.5|max:99999999.99',
            "delivery_generic" => 'nullable|max:255|string',
            "supplier_generic" => 'nullable|max:255|string',
            "price_used" => 'nullable|numeric|min:0.5|max:99999999.99',
            "delivery_used" => 'nullable|max:255|string',
            "supplier_used"=> 'nullable|max:255|string',
            "part_uuid" => [
                'required',
                'string',
                'uuid',
            ],
        ];
    }
}
