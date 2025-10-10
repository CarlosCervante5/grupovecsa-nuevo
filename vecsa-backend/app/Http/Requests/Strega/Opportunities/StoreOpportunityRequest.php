<?php

namespace App\Http\Requests\Strega\Opportunities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOpportunityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'q_model_interest' => (string) $this->input('q_model_interest'),
            'q_brand_interest' => (string) $this->input('q_brand_interest'),
            // 'fu_survey_csi' => (string) $this->input('fu_survey_csi'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            
            'name' => 'required|max:255|string',
            'last_name' => 'nullable|max:255|string',
            'email' => 'nullable|max:255|string',
            'phone' => 'required|numeric',
            'educational_level' => 'nullable|string|max:255|string',
            'contact_method' => 'nullable|max:255|string',
            'honorific' => 'nullable|max:255|string',
            'crm_id' => 'nullable|max:255|string',

            'gender' => 'nullable|max:255|string',
            'rfc' => 'nullable|max:255|string',
            'marital_status' => 'nullable|max:255|string',
            'educational_level' => 'nullable|max:255|string',
            'address' => 'nullable|max:255|string',
            'neighborhood' => 'nullable|max:255|string',
            'zip_code' => 'nullable|numeric',
            'state' => 'nullable|max:255|string',
            'district' => 'nullable|max:255|string',

            'q_model_interest' => 'nullable',
            'q_brand_interest' => 'nullable',
            'q_time_to_buy' => 'nullable|max:255|string',
            'q_type_of_buy' => 'nullable|max:255|string',
            'q_initial_investment' => 'nullable|max:255|string',
            'q_comments' => 'nullable|max:1000|string',

            'opportunity_type' => 'required|max:255|string',
            'opportunity_date' => 'sometimes|max:255|string',
            'opportunity_id' => 'nullable|max:255|string',
            'opportunity_category' => 'nullable|max:255|string', // (RFO, RFI, etc)
            'dealership_name' => 'required|max:255|string',

            'campaign_name' => 'required|max:255|string',
            'campaign_channel' => 'required|max:255|string',
            'campaign_source' => 'required|max:255|string',

            // Contact attempts
            'contact_station_name' => 'nullable|max:255|string', // nombre de quien atendio
            'contact_take_date' => 'nullable|max:255|string', // fecha de toma del lead
            'contact_comment' => 'nullable|max:1000|string', // 
            'contact_attempt_status' => 'nullable|max:255|string', // contactado si, no
            'contact_attempt_description' => 'nullable|max:1000|string', // razon de no contactado (resultado)
            'contact_transfer_attempt_status' => 'nullable|max:255|string', // enlace directo (si, no)
            'contact_transfer_attempt_description' => 'nullable|max:3000|string', // razon de no contactado (resultado)
            'contact_channel' => 'nullable|max:255|string', // medio por el que fue contactado

            // appointments
            'appointment_assignment' => 'nullable|max:255|string', // Asignacion (si, no)
            'appointment_date' => 'nullable|max:255|string', // Fecha de asigación
            'appointment_seller' => 'nullable|max:255|string', // Ejecutivo asignado

            // Agregar campos de follow survey
            'fu_survey_status' => 'nullable|max:255|string', // Asignacion (si, no)
            'fu_survey_comment' => 'nullable|max:1000|string', // Fecha de asigación
            'fu_survey_ticket_complain' => 'nullable|max:255|string', // Ejecutivo asignado
            'fu_survey_csi' => 'nullable',
            'fu_survey_satisfaction' => 'nullable|max:255|string', // Ejecutivo asignado
            'fu_survey_satisfaction_comments' => 'nullable|max:3000|string', // Ejecutivo asignado

        ];
    }
}
