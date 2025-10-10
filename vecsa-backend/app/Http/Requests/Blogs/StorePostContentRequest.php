<?php

namespace App\Http\Requests\Blogs;

use Illuminate\Foundation\Http\FormRequest;

class StorePostContentRequest extends FormRequest
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
            'post_uuid' => 'required|uuid',
            'type_content' => 'required|string',
            'content_text' => 'required|string|max:65535',
            'content_multimedia_1' => 'sometimes|image|mimes:jpeg,png,jpg,gif,pdf|max:5128',
            'content_multimedia_2' => 'sometimes|image|mimes:jpeg,png,jpg,gif,pdf|max:5128',
        ];
    }
}
