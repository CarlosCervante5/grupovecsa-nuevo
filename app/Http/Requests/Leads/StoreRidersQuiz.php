<?php

namespace App\Http\Requests\Leads;

use Illuminate\Foundation\Http\FormRequest;

class StoreRidersQuiz extends FormRequest
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
            'email' => 'required|string',
            'phone' => 'required|numeric',
            'model' => 'required|string',
            'gloves' => 'required|in:ch,m,g,xl',
            'jacket' => 'required|in:ch,m,g,xl',
            'footwear' => 'required|string',
            'helmet' => 'required|in:ch,m,g,xl',
            'color' => 'required|string',
        ];
    }
}
