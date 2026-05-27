<?php

namespace App\Http\Requests\Users;

use App\Support\UserDealershipRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{   
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('password') && trim((string) $this->input('password')) === '') {
            $this->merge(['password' => null]);
        }
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
            'user_uuid' => [
                'required',
                'string',
                'exists:users,uuid',
            ],
            'name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user_uuid, 'uuid'),
            ],
            'phone_1' => 'nullable|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,H,M',
            'location' => 'nullable|string|max:90',
            'dealership_ids' => 'sometimes|array',
            'dealership_ids.*' => 'integer|exists:' . $prefix . 'dealerships,id',
            'role_name' => 'sometimes|required|string|max:255',
            'password' => [
                'nullable',
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
            if (! $this->has('dealership_ids') && ! $this->has('role_name')) {
                return;
            }
            $role = strtolower(trim((string) $this->input('role_name', '')));
            if ($role === '' && $this->filled('user_uuid')) {
                $user = \App\Models\User::findByUuid($this->input('user_uuid'));
                $role = strtolower((string) ($user?->getRoleNames()->first() ?? ''));
            }
            if (in_array($role, ['administrator', 'developer'], true)) {
                return;
            }
            $ids = $this->input('dealership_ids', []);
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
    public function messages(): array
    {
        return [
            'password.regex' => 'La contraseña debe contener al menos una letra minúscula, una letra mayúscula, un dígito y un carácter especial (@$!%*?&).',
        ];
    }
}
