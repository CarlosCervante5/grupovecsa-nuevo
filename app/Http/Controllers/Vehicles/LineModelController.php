<?php

namespace App\Http\Controllers\Vehicles;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicles\Models\StoreLineModelRequest;
use App\Http\Requests\Vehicles\Models\UpdateLineModelRequest;
use App\Models\BrandLine;
use App\Models\LineModel;
use App\Models\VehicleBrand;
use Illuminate\Validation\ValidationException;

class LineModelController extends Controller
{
    /**
     * Obtener una lista de todos los modelos de la línea.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Obtener todos los modelos de la línea
            $lineModels = LineModel::all();

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(200, 'Modelos de la linea obtenidos exitosamente', ['line_models' => $lineModels]);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener la lista de modelos de la línea', $e->getMessage(), 500, 'GET_LINE_MODEL_ERROR');
        }
    }

    /**
     * Crear un nuevo modelo de la línea.
     *
     * @param  \App\Http\Requests\Users\StoreLineModelRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreLineModelRequest $request)
    {
        try {
            // Validar los datos recibidos
            $data = $request->validated();

            // Crear un nuevo modelo de la línea
            $lineModel = LineModel::create($data);

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Modelo de línea creada exitosamente', $lineModel);

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al crear el modelo de la línea', $e->getMessage(), 500, 'CREATE_LINE_MODEL_ERROR');
        }
    }

    /**
     * Obtener un modelo específico de la línea por ID.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {

            $lineModel = LineModel::where('id', $id)->first();
        
            if (!$lineModel) {

                return ApiResponseHelper::apiError('El modelo de la línea no existe', 'No existe el id: '. $id ,404, 'GET_LINE_MODEL_ERROR');
            }

            // Retornar el modelo de linea encontrado
            return ApiResponseHelper::apiSuccess(200, 'Modelo de linea encontrado', $lineModel);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener el modelo de la línea', $e->getMessage(), 500, 'GET_LINE_MODEL_ERROR');
        }
    }

    /**
     * Actualizar un modelo específico de la línea por ID en la base de datos.
     *
     * @param  \App\Http\Requests\Users\UpdateLineModelRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateLineModelRequest $request, $id)
    {
        // La marca ya ha sido encontrado en UpdateLineModelRequest
        $lineModel = $request->lineModelModel;

        // Validar los datos recibidos
        $data = $request->validated();

        // Actualizar la marca con los datos validados
        $lineModel->update($data);

        // Devolver respuesta de éxito
        return ApiResponseHelper::apiSuccess(200, 'Modelo de línea actualizada exitosamente', $lineModel);
    }

    /**
     * Eliminar un modelo de la línea específica por ID.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // Buscar la el modelo de la linea por ID
            $lineModel = LineModel::find($id);

            if (!$lineModel) {
                // Retornar respuesta de error si el modelo de la linea no se encuentra
                return ApiResponseHelper::apiError('Modelo de línea no encontrada', null, 404, 'LINE_MODEL_NOT_FOUND');
            }

            // Eliminar el modelo de la linea
            $lineModel->delete();
            return ApiResponseHelper::apiSuccess(200, 'Modelo de línea eliminada exitosamente');

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Hubo un error al eliminar el modelo de la línea', $e->getMessage(), 500, 'DELETE_LINE_MODEL_ERROR');
        }
    }

    /**
     * Obtener modelos de linea por el nombre de la linea
     *
     * @param  string  $linea
     * @return \Illuminate\Http\JsonResponse
     */
    public function byLine($line)
    {
        try {

            $line = BrandLine::where('name', $line)->first();

            // Verificar si la linea existe
            if (!$line) {
                return ApiResponseHelper::apiError('La linea no existe', 'No existe la linea con el nombre: '. $line, 404, 'GET_LINE_MODEL_ERROR');
            }

            // Obtener los modelos de linea relacionados con la linea encontrada
            $lineModels = LineModel::where('line_id', $line->id)->get();

            // Verificar si existen líneas de marca
            if ($lineModels->isEmpty()) {
                return ApiResponseHelper::apiError('No se encontraron los modelos de linea para la linea especificada', null, 404, 'GET_LINE_MODEL_ERROR');
            }

            // Retornar la línea de marca encontrada
            return ApiResponseHelper::apiSuccess(200, 'Modelos de linea encontrados', ['line_models' => $lineModels]);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener los modelos de linea', $e->getMessage(), 500, 'GET_LINE_MODEL_ERROR');
        }
    }

    /**
     * Obtener modelos de linea por el nombre de la marca // Por eliminar
     *
     * @param  string  $linea
     * @return \Illuminate\Http\JsonResponse
     */
    public function byBrand($brand)
    {
        try {

            $brand = VehicleBrand::where('name', $brand)->first();

            // Verificar si la linea existe
            if (!$brand) {
                return ApiResponseHelper::apiError('La marca no existe', 'No existe la marca con el nombre: '. $brand, 404, 'GET_VEHICLE_BRAND_ERROR');
            }

            // Obtener los modelos de linea relacionados con la linea encontrada
            $lines = $brand->lines;

            $lineModels = collect();

            foreach ($lines as $line) {
                $lineModels = $lineModels->merge($line->models); // Obtienes los modelos de cada línea
            }

            // Verificar si existen líneas de marca
            if ($lineModels->isEmpty()) {
                return ApiResponseHelper::apiError('No se encontraron los modelos de linea para la marca especificada', null, 404, 'GET_LINE_MODEL_ERROR');
            }

            // Retornar la línea de marca encontrada
            return ApiResponseHelper::apiSuccess(200, 'Modelos de linea encontrados', ['line_models' => $lineModels]);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener los modelos de linea', $e->getMessage(), 500, 'GET_LINE_MODEL_ERROR');
        }
    }
}
