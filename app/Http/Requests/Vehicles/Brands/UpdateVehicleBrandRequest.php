<?php

namespace App\Http\Requests\Vehicles\Brands;

use App\Helpers\ApiResponseHelper;
use App\Models\VehicleBrand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleBrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        $id = $this->route('id');
        
        // Buscar la marca por ID y lanzar excepción si no se encuentra
        $vehicleBrand = VehicleBrand::find($id);
        
        if (!$vehicleBrand) {

            ApiResponseHelper::apiError('Error al actualizar la marca del vehículo', 'No existe la marca con id: ' . $id, 404, 'GET_VEHICLE_BRAND_ERROR');
            return false;
        }

        $this->vehicleBrandModel = $vehicleBrand;

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
                Rule::unique(env('DB_TABLE_PREFIX', '') . 'vehicle_brands')->where(function ($query) {
                    return $query->whereRaw('lower(name) = ?', [strtolower($this->name)]);
                })->ignore($this->vehicleBrandModel->id),
            ],
            'image_path' => 'nullable|string'
        ];
    }
}
