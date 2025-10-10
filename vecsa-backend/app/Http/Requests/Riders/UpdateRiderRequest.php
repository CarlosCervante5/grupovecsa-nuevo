<?php

namespace App\Http\Requests\Riders;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRiderRequest extends FormRequest
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
            'reward_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'vehicle_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'model' => 'nullable|max:255|string',
            'year' => 'nullable|integer',
            'vin' => 'nullable|max:255|string',
            'status' => 'nullable|max:255|string',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'model' => $this->model ?? null,
            'year' => $this->year ?? null,
            'vin' => $this->vin ?? null,
            'status' => $this->status ?? null,
        ]);
    }
}