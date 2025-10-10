<?php

namespace App\Http\Requests\Vehicles;

use App\Rules\ActiveVehicleExists;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
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
            'name' => 'nullable|max:255|string',
            'description' => 'nullable|max:1000|string',
            'vin' => 'required|max:255|string',
            'page_status' => 'nullable|in:active,inactive,sale,valuing',
            'purchase_date' => 'nullable|max:255|string',
            'sale_price' => 'nullable|numeric',
            'list_price' => 'nullable|numeric',
            'offer_price' => 'nullable|numeric',
            'mileage' => 'nullable|integer',
            'type' => 'nullable|in:car,moto,truck,other',
            'category' => 'nullable|in:new,pre_owned,demo',
            'cylinders' => 'nullable|integer',
            'interior_color' => 'nullable|max:255|string',
            'exterior_color' => 'nullable|max:255|string',
            'transmission' => 'nullable|in:manual,automatic,semiautomatic,cvt,triptronic,dual-clutch',
            'fuel_type' => 'nullable|in:gasoline,diesel,electric,hybrid,hydrogen,natural_gas',
            'drive_train' => 'nullable|max:255|string',
            'spec_sheet' => 'nullable|max:255|string',
            'brand' => 'required|max:255|string',
            'line' => 'required|max:255|string',
            'model' => 'required|max:255|string',
            'version' => 'required|max:255|string',
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
            'intake_engine' => 'nullable|string',
            'uuid' => [
                'required',
                'string',
                'uuid',
                new ActiveVehicleExists,
            ]
        ];
    }
}
