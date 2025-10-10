<?php

namespace App\Http\Requests\Riders;

use Illuminate\Foundation\Http\FormRequest;

class SearchRiderRequest extends FormRequest
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
    public function rules()
    {
        return [
            'type' => 'required|string|nullable',
            'keyword' => 'sometimes|string|nullable',
            'paginate' => 'sometimes|integer|min:1',
        ];
    }


    protected function prepareForValidation()
    {

        $this->merge([
            'type' => $this->input('type','valuation'),
            'keyword' => $this->input('keyword', ''),
            'paginate' => $this->input('paginate', 15),
        ]);
    }
}