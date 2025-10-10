<?php

namespace App\Http\Requests\Strega\Opportunities;

use Illuminate\Foundation\Http\FormRequest;

class FollowAttemptRequest extends FormRequest
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
            
            // Contact attempts
            'contact_attempt_status' => 'required|max:255|string', // contactado si, no
            'contact_attempt_description' => 'nullable|max:1000|string', // razon de no contactado (resultado)
            'contact_channel' => 'nullable|max:255|string', // medio por el que fue contactado
            'comments' => 'required|max:1000|string',

            'fu_survey_ticket_complain' => 'nullable|max:255|string',
            'fu_survey_csi' => 'nullable|max:255|string',
            'fu_survey_satisfaction' => 'nullable|max:255|string',

            'appointment_uuid' => [
                'required',
                'string',
                'uuid',
            ],

            'opportunity_uuid' => [
                'required',
                'string',
                'uuid',
            ],

        ];
    }
}
