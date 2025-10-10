<?php

namespace App\Http\Requests\Valuations;

use Illuminate\Foundation\Http\FormRequest;

class SearchValuationsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'keyword' => 'sometimes|string|nullable',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'keyword' => $this->input('keyword', ''),
        ]);
    }
}