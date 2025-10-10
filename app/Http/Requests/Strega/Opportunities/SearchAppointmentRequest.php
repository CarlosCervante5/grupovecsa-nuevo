<?php

namespace App\Http\Requests\Strega\Opportunities;

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
            'relationship_names' => 'sometimes|nullable',
            'keyword' => 'sometimes|string',
            'status' => 'sometimes|nullable',

            'by_manager' => 'sometimes|nullable',
            'by_type' => 'sometimes|nullable',
            'by_dealership' => 'sometimes|nullable',

            'has_appointments' => 'sometimes|boolean',
            
            'attempts_type' => 'sometimes|string',

            'order_by' => 'sometimes|string|nullable',
            'paginate' => 'sometimes|integer|min:1',
        ];
    }

    protected function prepareForValidation()
    {
        $stringToArray = function ($string) {
            return $string ? explode(',', $string) : [];
        };

        $this->merge([
            'relationship_names' => $this->has('relationship_names')
                ? $stringToArray($this->input('relationship_names'))
                : ['campaign', 'customer', 'manager.userProfile', 'appointments.stregaSeller.userProfile'],

            'status' => ($this->has('status') && !empty($this->input('status')))
                ? $stringToArray($this->input('status'))
                : [],

            'by_manager' => $this->input('by_manager', ''),

            'by_type' => $this->input('by_type', ''),

            'by_dealership' => $this->input('by_dealership', ''),

            'attempts_type' => $this->input('attempts_type', 'first_contact'),

            'has_appointments' => $this->has('has_appointments') ? $this->boolean('has_appointments') : false,

            'keyword' => $this->input('keyword', ''),

            'order_by' => $this->input('order_by', ''),

            'paginate' => $this->input('paginate', 15),
        ]);
    }
}
