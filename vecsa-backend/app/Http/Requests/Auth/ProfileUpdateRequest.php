<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
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
            'uuid' => [
                'required',
                'string',
                'uuid',
                'exists:users,uuid',
            ],
            'name' => 'required|string|max:90',
            'last_name' => 'required|string|max:90',
            'nickname' => 'required|string|max:90|regex:/^[a-zA-ZÀ-ÿ0-9_ .]+$/',
            'email_2' => 'nullable|string|email|max:90|unique:users,email',
            'phone_1' => 'nullable|integer',
            'phone_2' => 'nullable|integer', 
            'gender' => 'nullable|in:male,female,H,M',
            'birthday' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [

        ];
    }
}
