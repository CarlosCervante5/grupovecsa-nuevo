<?php

namespace App\Http\Requests\Vehicles\Versions;

use App\Helpers\ApiResponseHelper;
use App\Models\ModelVersion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModelVersionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        $id = $this->route('id');
        
        // Buscar la marca por ID y lanzar excepción si no se encuentra
        $modelVersion = ModelVersion::find($id);
        
        if (!$modelVersion) {

            ApiResponseHelper::apiError('Error al actualizar la version del modelo', 'No existe la version con id: ' . $id, 404, 'GET_MODEL_VERSION_ERROR');
            return false;
        }

        $this->modelVersionModel = $modelVersion;

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
                Rule::unique(env('DB_TABLE_PREFIX', '') . 'model_versions')->where(function ($query) {
                    return $query->whereRaw('lower(name) = ?', [strtolower($this->name)]);
                })->ignore($this->modelVersionModel->id),
            ],
            'image_path' => 'nullable|string',
            'description' => 'nullable|string',
            'model_id' => [
                'nullable',
                'integer',
                Rule::exists(env('DB_TABLE_PREFIX', '') . 'line_models', 'id')
            ],
        ];
    }
}
