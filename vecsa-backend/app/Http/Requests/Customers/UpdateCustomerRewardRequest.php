<?php

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRewardRequest extends FormRequest
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
            'gender_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'size_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'size' => 'required|max:255|string',
            'gender' => 'required|max:255|string',
            'origin_agency' => 'required|max:255|string',
            'email_1' => 'required|string|email|max:255|string',
            'phone_1' => 'required|max:255|string',
            'last_name' => 'required|max:255|string',
            'name' => 'required|max:255|string',
            'year' => 'required|integer',
            'model' => 'required|max:255|string',
        ];
    }
}