<?php

namespace App\Http\Requests\Vehicles\Models;

use App\Helpers\ApiResponseHelper;
use App\Models\LineModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLineModelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        $id = $this->route('id');
        
        // Buscar la marca por ID y lanzar excepción si no se encuentra
        $lineModel = LineModel::find($id);
        
        if (!$lineModel) {

            ApiResponseHelper::apiError('Error al actualizar el modelo de la línea', 'No existe el modelo con id: ' . $id, 404, 'GET_LINE_MODEL_ERROR');
            return false;
        }

        $this->lineModelModel = $lineModel;

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
                Rule::unique(env('DB_TABLE_PREFIX', '') . 'line_models')->where(function ($query) {
                    return $query->whereRaw('lower(name) = ?', [strtolower($this->name)]);
                })->ignore($this->lineModelModel->id),
            ],
            'image_path' => 'nullable|string',
            'brand_id' => [
                'nullable',
                'integer',
                Rule::exists(env('DB_TABLE_PREFIX', '') . 'brand_lines', 'id')
            ],
        ];
    }
}
