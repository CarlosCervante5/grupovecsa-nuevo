<?php

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
            'customer_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'honorific' => 'nullable|string|max:15',
            'bp_id' => 'nullable|string|max:90',
            'crm_id' => 'nullable|string|max:90',
            'rfc' => 'nullable|string|max:90|regex:/^[A-ZÑ0-9]{12}|[A-ZÑ0-9]{13}$/',
            'tax_regime' => 'nullable|string|max:90',
            'name' => 'required|string|max:90',
            'last_name' => 'required|string|max:90',
            'age' => 'nullable|integer|min:0',
            'birthday' => 'nullable|string',
            'gender' => 'nullable|in:male,female,H,M',
            'phone_1' => 'nullable|integer',
            'phone_2' => 'nullable|integer', 
            'phone_3' => 'nullable|integer', 
            'cellphone' => 'nullable|integer', 
            'email_1' => 'nullable|email|max:90',
            'email_2' => 'nullable|email|max:90',
            'contact_method' => 'nullable|string|max:90',
            'zip_code' => 'nullable|string|max:90',
            'address' => 'nullable|string|max:90',
            'state' => 'nullable|string|max:90',
            'city' => 'nullable|string|max:90',
            'district' => 'nullable|string|max:90',
            'neighborhood' => 'nullable|string|max:90',
            'marital_status' => 'nullable|string|max:90',
            'educational_level' => 'nullable|string|max:90',
            'origin_agency' => 'nullable|string|max:90',
        ];
    }
}
