<?php

namespace App\Http\Requests\Leads;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarCareRequest extends FormRequest
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
            'name' => 'required|string',
            'email_1' => 'nullable|string',
            'phone_1' => 'required|string',
            'model_name' => 'required|string',
            'year' => 'required|numeric',
            'dealership_name' => 'required|string',
            'required_service' => 'required|string',
            'comments' => 'nullable|string',
            'brand_name' => 'required|string',
            'appointment_date' => 'required|string',
        ];
    }
}
