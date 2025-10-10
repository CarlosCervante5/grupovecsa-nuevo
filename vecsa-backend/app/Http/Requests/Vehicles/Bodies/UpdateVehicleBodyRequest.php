<?php

namespace App\Http\Requests\Vehicles\Bodies;

use App\Helpers\ApiResponseHelper;
use App\Models\VehicleBody;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleBodyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        $id = $this->route('id');
        
        // Buscar la marca por ID y lanzar excepción si no se encuentra
        $vehicleBody = VehicleBody::find($id);
        
        if (!$vehicleBody) {

            ApiResponseHelper::apiError('Error al actualizar la carrocería del vehículo', 'No existe la carrocería con id: ' . $id, 404, 'GET_VEHICLE_BODY_ERROR');
            return false;
        }

        $this->vehicleBodyModel = $vehicleBody;

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
                Rule::unique(env('DB_TABLE_PREFIX', '') . 'vehicle_bodies')->where(function ($query) {
                    return $query->whereRaw('lower(name) = ?', [strtolower($this->name)]);
                })->ignore($this->vehicleBodyModel->id),
            ],
            'image_path' => 'nullable|string',
        ];
    }
}
