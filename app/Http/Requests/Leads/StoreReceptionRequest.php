<?php

namespace App\Http\Requests\Leads;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceptionRequest extends FormRequest
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
            'date'                 => 'nullable|string',
            'hour'                 => 'nullable|string',
            'salesAdvisor'         => 'nullable|string',
            'brand'                => 'nullable|string',
            'departureTime'        => 'nullable|string',
            'visitType'            => 'nullable|string',
            'visitFirsTime'        => 'nullable|string',
            'department'           => 'nullable|string',
            'clientName'           => 'nullable|string|max:255',
            'clientAge'            => 'nullable|string',
            'clientPhone'          => 'nullable|string',
            'howFindOut'           => 'nullable|string',
            'contactSub'           => 'nullable|string',
            'brandSecondOption'    => 'nullable|string',
            'modelSecondOption'    => 'nullable|string',
            'colorSecondOption'    => 'nullable|string',
            'customersCurrentCar'  => 'nullable|string',
            'versionSecondOption'  => 'nullable|string',
            'clientEmail'          => 'nullable|string|max:255',
            'preferredMedium'      => 'nullable|string',
            'model'                => 'nullable|string|max:255',
            'year'                 => 'nullable|integer',
            'version'              => 'nullable|string|max:255',
            'color'                => 'nullable|string',
            'accessories'          => 'nullable|string',
            'testDrive'            => 'nullable|string',
            'receivedQuote'        => 'nullable|string',
            'FAndI'                => 'nullable|string',
            'leaveCarOnAccount'    => 'nullable|string',
            'hostes'               => 'nullable|string',
            'wasClientProfile'     => 'nullable|string',
            'wasApplicationTaken'  => 'nullable|string',
            'financingType'        => 'nullable|string',
            'initialInvestment'    => 'nullable|string',
            'monthlyPayment'       => 'nullable|string',
            'termHowManyMonths'    => 'nullable|string',
            'segment'              => 'nullable|string',
            'idCRM'                => 'nullable|integer',
            'honorific'            => 'nullable|string',
            'contactSalesplace'    => 'nullable|string',
            'saleType'             => 'nullable|string',
        ];
    }
}
