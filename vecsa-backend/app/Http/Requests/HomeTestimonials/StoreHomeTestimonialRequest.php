<?php

namespace App\Http\Requests\HomeTestimonials;

use Illuminate\Foundation\Http\FormRequest;

class StoreHomeTestimonialRequest extends FormRequest
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
            'image' => 'required|file|uploadable_image|max:5120',
            'alt' => 'nullable|string',
        ];
    }
}
