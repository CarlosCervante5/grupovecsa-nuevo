<?php

namespace App\Http\Requests\Multimedia;

use Illuminate\Foundation\Http\FormRequest;

class StoreMultimediaRequest extends FormRequest
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
            'event_uuid' => 'required|uuid',
            'multimedia' => 'required|array',
            'multimedia.*' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
        ];
    }

    public function messages(): array
    {
        return [
            'multimedia.*.mimes' => 'Los archivos deben ser imágenes (jpeg, png, jpg, gif) o videos (mp4, mov, avi).',
            'multimedia.*.max' => 'Cada archivo no debe superar los 20MB.',
        ];
    }
}
