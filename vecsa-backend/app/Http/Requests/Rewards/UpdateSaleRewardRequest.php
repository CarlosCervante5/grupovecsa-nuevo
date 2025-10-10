<?php

namespace App\Http\Requests\Rewards;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSaleRewardRequest extends FormRequest
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
            'reward_uuid' => [
                'required',
                'string',
                'uuid'
            ],
            'customer_uuid' => [
                'required',
                'string',
                'uuid'
            ],
            'origin' => 'required|max:255|string',
            'quantity' => 'nullable|numeric',
            'sale_id' => 'nullable|max:255|string',
        ];
    }
}