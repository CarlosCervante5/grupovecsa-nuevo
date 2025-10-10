<?php

namespace App\Http\Requests\Strega\Opportunities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOpportunityRequest extends FormRequest
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
            
            'customer_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'name' => 'required|max:255|string',
            'last_name' => 'nullable|max:255|string',
            'email' => 'nullable|max:255|string',
            'phone' => 'required|numeric',
            'educational_level' => 'nullable|string|max:255|string',
            'contact_method' => 'nullable|max:255|string',
            'honorific' => 'nullable|max:255|string',
            'crm_id' => 'nullable|max:255|string',

            'q_model_interest' => 'nullable',
            'q_brand_interest' => 'nullable',
            'q_time_to_buy' => 'nullable|max:255|string',
            'q_type_of_buy' => 'nullable|max:255|string',
            'q_initial_investment' => 'nullable|max:255|string',
            'q_comments' => 'nullable|max:1000|string',

            'opportunity_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'opportunity_type' => 'required|max:255|string',
            'opportunity_id' => 'nullable|max:255|string',
            'opportunity_category' => 'nullable|max:255|string', // (RFO, RFI, etc)
            'dealership_name' => 'required|max:255|string',

            'campaign_name' => 'required|max:255|string',
            'campaign_channel' => 'required|max:255|string',
            'campaign_source' => 'required|max:255|string',

        ];
    }
}
