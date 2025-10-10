<?php

namespace App\Http\Requests\Valuations;

use Illuminate\Foundation\Http\FormRequest;

class AttatchCheckpointRequest extends FormRequest
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
            'checkpoint_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'valuation_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'selected_value' => 'required|string',
        ];
    }
}