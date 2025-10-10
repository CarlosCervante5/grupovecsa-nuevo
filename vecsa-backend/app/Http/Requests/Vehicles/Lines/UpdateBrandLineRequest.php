<?php

namespace App\Http\Requests\Vehicles\Lines;

use App\Helpers\ApiResponseHelper;
use App\Models\BrandLine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandLineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        $id = $this->route('id');
        
        // Buscar la marca por ID y lanzar excepción si no se encuentra
        $brandLine = BrandLine::find($id);
        
        if (!$brandLine) {

            ApiResponseHelper::apiError('Error al actualizar la linea de la marca', 'No existe la linea con id: ' . $id, 404, 'GET_BRAND_LINE_ERROR');
            return false;
        }

        $this->brandLineModel = $brandLine;

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
                })->ignore($this->brandLineModel->id),
            ],
            'image_path' => 'nullable|string',
            'brand_id' => [
                'nullable',
                'integer',
                Rule::exists(env('DB_TABLE_PREFIX', '') . 'vehicle_brands', 'id')
            ],
        ];
    }
}
