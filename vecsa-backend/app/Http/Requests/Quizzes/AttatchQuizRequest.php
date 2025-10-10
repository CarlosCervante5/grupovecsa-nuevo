<?php

namespace App\Http\Requests\Quizzes;

use Illuminate\Foundation\Http\FormRequest;

class AttatchQuizRequest extends FormRequest
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
            'quiz_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'customer_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'selected_value' => 'required|string',
        ];
    }
}