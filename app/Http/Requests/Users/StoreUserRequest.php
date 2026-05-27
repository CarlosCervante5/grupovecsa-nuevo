<?php

namespace App\Http\Requests\Users;

use App\Support\UserDealershipRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
        $prefix = env('DB_TABLE_PREFIX', '');

        return [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone_1' => 'nullable|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,H,M',
            'location' => 'nullable|string|max:90',
            'dealership_ids' => 'sometimes|array',
            'dealership_ids.*' => 'integer|exists:' . $prefix . 'dealerships,id',
            'role_name' => 'required|string|max:255',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-zñ])(?=.*[A-ZÑ])(?=.*\d)(?=.*[@$!%*?&])[A-Za-zÑñ\d@$!%*?&]+$/u'
            ],
            'image' => 'sometimes|file|uploadable_image|max:10128',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $role = strtolower(trim((string) $this->input('role_name', '')));
            if (in_array($role, ['administrator', 'developer'], true)) {
                return;
            }
            $ids = $this->input('dealership_ids', []);
            $location = trim((string) $this->input('location', ''));
            if ((! is_array($ids) || count($ids) === 0) && $location === '') {
                $validator->errors()->add('dealership_ids', 'Asigna al menos una sucursal.');
            }
            if (
                is_array($ids)
                && count($ids) > 1
                && ! UserDealershipRules::allowsMultipleDealerships($role)
            ) {
                $validator->errors()->add('dealership_ids', 'Este rol solo puede tener una sucursal asignada.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'password.regex' => 'La contraseña debe contener al menos una letra minúscula, una letra mayúscula, un dígito y un carácter especial (@$!%*?&).',
            'email.unique' => 'El correo electrónico ya está en uso. Por favor, elija otro para registrarse.'
        ];
    }
}
