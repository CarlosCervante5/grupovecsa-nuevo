<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
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
            'type' => 'required|max:255|string',
            'customer_uuid' => 'required|max:255|string',
            'brand_name' => 'required|max:255|string',
            'model_name' => 'required|max:255|string',
            'year' => 'required|integer',
            'mileage' => 'required|integer',
            'scheduled_date' => 'required|max:255|string',
            'dealership_name' => 'required|max:255|string'
        ];
    }
}
