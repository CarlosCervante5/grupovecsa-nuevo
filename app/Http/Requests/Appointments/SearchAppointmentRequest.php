<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class SearchAppointmentRequest extends FormRequest
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
            'category' => 'sometimes|string|nullable',
            'keyword' => 'sometimes|string|nullable',
            'paginate' => 'sometimes|integer|min:1',
        ];
    }


    protected function prepareForValidation()
    {

        $this->merge([
            'category' => $this->input('category','rider'),
            'keyword' => $this->input('keyword', ''),
            'paginate' => $this->input('paginate', 15),
        ]);
    }
}
