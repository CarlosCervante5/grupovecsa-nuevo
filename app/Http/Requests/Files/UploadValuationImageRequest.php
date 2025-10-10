<?php

namespace App\Http\Requests\Files;

use Illuminate\Foundation\Http\FormRequest;

class UploadValuationImageRequest extends FormRequest
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
            'valuation_uuid' => 'required|uuid',
            'name' => 'required|string',
            'group_name' => 'required|string',
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,pdf|max:5128',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'group_name' => strtolower($this->group_name),
        ]);
    }
}
