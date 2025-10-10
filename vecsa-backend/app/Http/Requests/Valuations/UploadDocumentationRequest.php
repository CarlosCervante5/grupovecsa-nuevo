<?php

namespace App\Http\Requests\Valuations;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'valuation_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'documentation_pdf' => 'required|mimes:pdf|max:2048'
        ];
    }
}