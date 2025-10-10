<?php

namespace App\Http\Requests\Riders;

use Illuminate\Foundation\Http\FormRequest;

class MileageRiderRequest extends FormRequest
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
            'customer_reward_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'initial_mileage' => 'nullable|integer',
            'final_mileage' => 'nullable|integer',
            'initial_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,pdf|max:5128',
            'final_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,pdf|max:5128',
            'purchase_amount' => 'nullable|numeric'
        ];
    }
}