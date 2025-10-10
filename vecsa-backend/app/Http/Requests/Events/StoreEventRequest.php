<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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

            'begin_date' => 'required|max:255|string',
            'end_date' => 'required|max:255|string',
            'name' => 'required|max:255|string',
            'description' => 'nullable|max:1000|string',
            'segment_name' => 'nullable|max:255|string',
            'type' => 'nullable|in:video,schedule,community,principal',
            'page_status' => 'nullable|in:public,clients,exclusive,unique,primary,offer,time_limit,other',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,pdf|max:5128',

        ];
    }


    protected function prepareForValidation()
    {
        $this->merge([
            'page_status' => $this->page_status ?? 'public',
        ]);
    }
}
