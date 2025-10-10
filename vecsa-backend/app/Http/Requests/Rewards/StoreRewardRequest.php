<?php

namespace App\Http\Requests\Rewards;

use Illuminate\Foundation\Http\FormRequest;

class StoreRewardRequest extends FormRequest
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
            'description' => 'nullable|max:1000|string',
            'begin_date' => 'nullable|max:255|string',
            'end_date' => 'nullable|max:255|string',
            'type' => ' nullable|max:255|string',
            'category' => 'nullable|max:255|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,pdf|max:5128',
        ];
    }
}