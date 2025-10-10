<?php

namespace App\Http\Requests\Strega\Opportunities;

use Illuminate\Foundation\Http\FormRequest;

class FirstAttemptRequest extends FormRequest
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
            
            'opportunity_uuid' => [
                'required',
                'string',
                'uuid',
            ],

            // Contact attempts
            'contact_attempt_status' => 'required|max:255|string', // contactado si, no
            'contact_attempt_description' => 'nullable|max:1000|string', // razon de no contactado (resultado)
            'contact_channel' => 'nullable|max:255|string', // medio por el que fue contactado
            'contact_transfer_attempt_status' => 'nullable|max:255|string', // enlace directo (si, no)

            // appointments
            'appointment_assignment' => 'nullable|max:255|string', // Asignacion (no, asignacion, transferencia)
            'appointment_date' => 'nullable|max:255|string', // Fecha de asigación

            'dealership_name' => 'nullable|max:255|string',

            'comments' => 'required|max:1000|string',

            'customer_uuid' => [
                'required',
                'string',
                'uuid',
            ],

            'seller_uuid' => [
                'nullable',
                'string',
                'uuid',
            ],

        ];
    }
}
