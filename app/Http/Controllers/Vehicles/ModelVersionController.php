<?php

namespace App\Http\Controllers\Vehicles;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicles\Versions\StoreModelVersionRequest;
use App\Http\Requests\Vehicles\Versions\UpdateModelVersionRequest;
use App\Models\LineModel;
use App\Models\ModelVersion;
use Illuminate\Validation\ValidationException;

class ModelVersionController extends Controller
{
    /**
     * Obtener una lista de todos las versiones de modelos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Obtener todos las versiones de modelos
            $modelVersions = ModelVersion::all();

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(200, 'Versiones de modelos obtenidas exitosamente', ['model_versions' => $modelVersions]);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener la lista de versiones de modelos', $e->getMessage(), 500, 'GET_MODEL_VERSION_ERROR');
        }
    }

    /**
     * Crear una nueva version de modelo.
     *
     * @param  \App\Http\Requests\Users\StoreModelVersionRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreModelVersionRequest $request)
    {
        try {
            // Validar los datos recibidos
            $data = $request->validated();

            // Crear una nueva version de modelo
            $modelVersion = ModelVersion::create($data);

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Version de modelo creada exitosamente', $modelVersion);

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al crear la version del modelo', $e->getMessage(), 500, 'CREATE_MODEL_VERSION_ERROR');
        }
    }

    /**
     * Obtener un modelo específico de carrocería por ID.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {

            $modelVersion = ModelVersion::where('id', $id)->first();
        
            if (!$modelVersion) {

                return ApiResponseHelper::apiError('La version no existe', 'No existe el id: '. $id ,404, 'GET_MODEL_VERSION_ERROR');
            }

            // Retornar la version del modelo encontrada
            return ApiResponseHelper::apiSuccess(200, 'Version de modelo encontrada', $modelVersion);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener la version del modelo', $e->getMessage(), 500, 'GET_MODEL_VERSION_ERROR');
        }
    }

    /**
     * Actualizar el modelo específica por ID en la base de datos.
     *
     * @param  \App\Http\Requests\Users\UpdateModelVersionRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateModelVersionRequest $request, $id)
    {
        // La version ya ha sido encontrada en UpdateModelVersionRequest
        $modelVersion = $request->modelVersionModel;

        // Validar los datos recibidos
        $data = $request->validated();

        // Actualizar la version con los datos validados
        $modelVersion->update($data);

        // Devolver respuesta de éxito
        return ApiResponseHelper::apiSuccess(200, 'Version actualizada exitosamente', $modelVersion);
    }

    /**
     * Eliminar la version específica por ID.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // Buscar la version por ID
            $modelVersion = ModelVersion::find($id);

            if (!$modelVersion) {
                // Retornar respuesta de error si la version no se encuentra
                return ApiResponseHelper::apiError('Version de modelo no encontrada', null, 404, 'MODEL_VERSION_NOT_FOUND');
            }

            // Eliminar la version
            $modelVersion->delete();
            return ApiResponseHelper::apiSuccess(200, 'Version de modelo eliminada exitosamente');

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Hubo un error al eliminar la version del modelo', $e->getMessage(), 500, 'DELETE_MODEL_VERSION_ERROR');
        }
    }

    /**
     * Obtener versiones de modelo por el nombre de la modelo
     *
     * @param  string  $linea
     * @return \Illuminate\Http\JsonResponse
     */
    public function byModel($model)
    {
        try {

            $model = LineModel::where('name', $model)->first();

            // Verificar si la linea existe
            if (!$model) {
                return ApiResponseHelper::apiError('El modelo no existe', 'No existe el modelo con el nombre: '. $model, 404, 'GET_MODEL_VERSION_ERROR');
            }

            // Obtener los modelos de linea relacionados con la linea encontrada
            $modelVersions = ModelVersion::where('model_id', $model->id)->get();

            // Verificar si existen líneas de marca
            if ($modelVersions->isEmpty()) {
                return ApiResponseHelper::apiError('No se encontraron las versiones de modelo para el modelo especificado', null, 404, 'GET_MODEL_VERSION_ERROR');
            }

            // Retornar la línea de marca encontrada
            return ApiResponseHelper::apiSuccess(200, 'Versiones de modelo encontradas', ['model_versions' => $modelVersions]);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener las versiones de modelo', $e->getMessage(), 500, 'GET_MODEL_VERSION_ERROR');
        }
    }
}
