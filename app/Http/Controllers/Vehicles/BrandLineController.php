<?php

namespace App\Http\Controllers\Vehicles;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponseHelper;
use App\Http\Requests\Vehicles\Lines\StoreBrandLineRequest;
use App\Http\Requests\Vehicles\Lines\UpdateBrandLineRequest;
use App\Models\BrandLine;
use App\Models\VehicleBrand;
use Illuminate\Validation\ValidationException;

class BrandLineController extends Controller
{
    /**
     * Obtener una lista de todos las líneas de marca.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Obtener todos las líneas de marca
            $brandLines = BrandLine::all();

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(200, 'Líneas de marca obtenidas exitosamente', ['brand_lines' => $brandLines]);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener la lista de líneas de marca', $e->getMessage(), 500, 'GET_BRAND_LINE_ERROR');
        }
    }

    /**
     * Crear una nueva línea de marca.
     *
     * @param  \App\Http\Requests\Users\StoreBrandLineRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreBrandLineRequest $request)
    {
        try {
            // Validar los datos recibidos
            $data = $request->validated();

            // Crear una nueva líneas de marca
            $brandLine = BrandLine::create($data);

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Línea de marca creada exitosamente', $brandLine);

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al crear la línea de marca', $e->getMessage(), 500, 'CREATE_BRAND_LINE_ERROR');
        }
    }

    /**
     * Obtener una línea de marca específica por ID.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {

            $brandLine = BrandLine::where('id', $id)->first();
        
            if (!$brandLine) {

                return ApiResponseHelper::apiError('La línea de marca no existe', 'No existe el id: '. $id ,404, 'GET_BRAND_LINE_ERROR');
            }

            // Retornar la línea de marca encontrada
            return ApiResponseHelper::apiSuccess(200, 'Línea de marca encontrada', $brandLine);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener la línea de marca', $e->getMessage(), 500, 'GET_BRAND_LINE_ERROR');
        }
    }

    /**
     * Actualizar una línea de marca específica por ID en la base de datos.
     *
     * @param  \App\Http\Requests\Users\UpdateBrandLineRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateBrandLineRequest $request, $id)
    {
        // La marca ya ha sido encontrado en UpdateBrandLineRequest
        $brandLine = $request->brandLineModel;

        // Validar los datos recibidos
        $data = $request->validated();

        // Actualizar la marca con los datos validados
        $brandLine->update($data);

        // Devolver respuesta de éxito
        return ApiResponseHelper::apiSuccess(200, 'Línea de marca actualizada exitosamente', $brandLine);
    }

    /**
     * Eliminar una línea de marca específica por ID.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // Buscar la línea de marca por ID
            $brandLine = BrandLine::find($id);

            if (!$brandLine) {
                // Retornar respuesta de error si la línea de marca no se encuentra
                return ApiResponseHelper::apiError('Línea de marca no encontrada', null, 404, 'BRAND_LINE_NOT_FOUND');
            }

            // Eliminar la línea de marca
            $brandLine->delete();
            return ApiResponseHelper::apiSuccess(200, 'Línea de marca eliminada exitosamente');

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Hubo un error al eliminar la líneas de marca', $e->getMessage(), 500, 'DELETE_BRAND_LINE_ERROR');
        }
    }

    /**
     * Obtener lineas de marca por el nombre de la marca
     *
     * @param  string  $brand
     * @return \Illuminate\Http\JsonResponse
     */
    public function byBrand($brand)
    {
        try {

            $brand = VehicleBrand::where('name', $brand)->first();

            // Verificar si la marca existe
            if (!$brand) {
                return ApiResponseHelper::apiError('La marca no existe', 'No existe la marca con el nombre: '. $brand, 404, 'GET_BRAND_LINE_ERROR');
            }

            // Obtener las líneas de marca relacionadas con la marca encontrada
            $brandLines = BrandLine::where('brand_id', $brand->id)->get();

            // Verificar si existen líneas de marca
            if ($brandLines->isEmpty()) {
                return ApiResponseHelper::apiError('No se encontraron líneas de marca para la marca especificada', null, 404, 'GET_BRAND_LINE_ERROR');
            }

            // Retornar la línea de marca encontrada
            return ApiResponseHelper::apiSuccess(200, 'Líneas de marca encontrada', ['brand_lines' => $brandLines]);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener la línea de marca', $e->getMessage(), 500, 'GET_BRAND_LINE_ERROR');
        }
    }
}
