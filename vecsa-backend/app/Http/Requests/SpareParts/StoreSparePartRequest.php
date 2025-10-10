<?php

namespace App\Http\Requests\SpareParts;

use Illuminate\Foundation\Http\FormRequest;

class StoreSparePartRequest extends FormRequest
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
            'name' => 'required|max:255|string',
            'code' => 'nullable|max:255|string',
            'quantity' => 'required|integer',
            'labor_time'  => 'required|numeric|min:0.5|max:99999999.99',
            'valuation_uuid' => [
                'required',
                'string',
                'uuid',
            ],
        ];
    }
}
