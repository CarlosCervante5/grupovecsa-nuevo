<?php

namespace App\Http\Requests\Valuations;

use Illuminate\Foundation\Http\FormRequest;

class StoreValuationVehicleRequest extends FormRequest
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
            'valuation_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'technician_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'vin' => 'required|max:255|string',
            'page_status' => 'required|in:valuing',
            'mileage' => 'required|integer',
            'type' => 'required|in:car,moto,truck,other',
            'cylinders' => 'required|integer',
            'exterior_color' => 'required|max:255|string',
            'transmission' => 'required|in:manual,automatic,semiautomatic,cvt,triptronic,dual-clutch,others',
            'drive_train' => 'nullable|max:255|string',
            'brand' => 'required|max:255|string',
            'model' => 'required|max:255|string',
            'version' => 'required|max:255|string',
            'line' => 'required|max:255|string',
            'body' => 'required|max:255|string',
            'dealership_name' => 'required|max:255|string',
            'location' => 'required|max:255|string',
            'year' => 'required|integer',
            'keys_number' => 'nullable|integer', 
            'wheel_locks' => 'nullable|in:yes,no', 
            'spare_wheel' => 'nullable|in:yes,no', 
            'hydraulic_jack' => 'nullable|in:yes,no', 
            'fire_extinguisher' => 'nullable|in:yes,no', 
            'reflectors' => 'nullable|in:yes,no', 
            'jumper_cables' => 'nullable|in:yes,no', 
            'engine_type' => 'nullable|string', 
            'plates' => 'nullable|string', 
            'country_of_origin' => 'nullable|string', 
            'auto_start_stop' => 'nullable|in:yes,no',
            'tools' => 'nullable|in:yes,no',
            'antenna' => 'nullable|in:yes,no',
            'stud_wrench' => 'nullable|in:yes,no',
            'security_film' => 'nullable|in:yes,no',
            'warranty_policy' => 'nullable|in:yes,no',
            'warranty_manual' => 'nullable|in:yes,no',
            'intake_engine' => 'nullable|string'
        ];
    }
}
