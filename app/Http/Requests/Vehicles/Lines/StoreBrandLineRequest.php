<?php

namespace App\Http\Requests\Vehicles\Lines;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBrandLineRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                Rule::unique(env('DB_TABLE_PREFIX', '') . 'brand_lines')->where(function ($query) {
                    return $query->whereRaw('lower(name) = ?', [strtolower($this->name)])
                                 ->whereNull('deleted_at');
                }),
            ],
            'image_path' => 'nullable|string',
            'brand_id' => [
                'nullable',
                'integer',
                Rule::exists(env('DB_TABLE_PREFIX', '') . 'vehicle_brands', 'id')
            ],
        ];
    }
}
