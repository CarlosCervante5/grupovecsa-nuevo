<?php

namespace App\Http\Requests\Leads;

use App\Rules\ActiveVehicleExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAskInfomationRequest extends FormRequest
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
            'lastname' => 'max:255|string',
            'email' => 'required|max:255|string',
            'phone_1' => 'required|integer',
            'message' => 'string',
            'vehicle_name' => 'string',
            'dealership_name' => 'string',
            'vehicles_uuid' => 'array|min:1', 
            'vehicles_uuid.*' => [
                'required',
                'string',
                'uuid',
                new ActiveVehicleExists,
            ]
        ];
    }
}
