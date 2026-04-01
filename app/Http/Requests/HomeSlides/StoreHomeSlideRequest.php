<?php

namespace App\Http\Requests\HomeSlides;

use Illuminate\Foundation\Http\FormRequest;

class StoreHomeSlideRequest extends FormRequest
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
            'title' => 'required|string',
            'subtitle' => 'nullable|string',
            'offer_main' => 'nullable|string',
            'offer_main_text' => 'nullable|string',
            'offer_sub' => 'nullable|string',
            'offer_secondary' => 'nullable|string',
            'offer_secondary_text' => 'nullable|string',
            'button_text' => 'nullable|string',
            'button_link' => 'nullable|string',
            'disclaimer' => 'nullable|string',
            'desktop_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'mobile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }
}
