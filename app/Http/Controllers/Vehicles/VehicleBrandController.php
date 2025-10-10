<?php

namespace App\Http\Controllers\Vehicles;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicles\Brands\StoreVehicleBrandRequest;
use App\Http\Requests\Vehicles\Brands\UpdateVehicleBrandRequest;
use App\Models\VehicleBrand;
use Illuminate\Validation\ValidationException;

class VehicleBrandController extends Controller
{
    /**
     * Obtener una lista de todos las marcas de vehículos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Obtener todos las marcas de vehículos
            $vehicleBrands = VehicleBrand::all();

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(200, 'Marcas de vehículo obtenidas exitosamente', ['vehicle_brands' => $vehicleBrands]);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener la lista de marcas de vehículo', $e->getMessage(), 500, 'GET_VEHICLE_BRANDS_ERROR');
        }
    }

    /**
     * Crear una nueva marca de vehículo.
     *
     * @param  \App\Http\Requests\Users\StoreVehicleBrandRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreVehicleBrandRequest $request)
    {
        try {
            // Validar los datos recibidos
            $data = $request->validated();

            // Crear una nueva marca de vehículo
            $vehicleBrand = VehicleBrand::create($data);

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Marca de vehículo creada exitosamente', $vehicleBrand);

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al crear la marca del vehículo', $e->getMessage(), 500, 'CREATE_VEHICLE_BRAND_ERROR');
        }
    }

    /**
     * Obtener una marca de vehículo específica por ID.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {

            $vehicleBrand = VehicleBrand::where('id', $id)->first();
        
            if (!$vehicleBrand) {

                return ApiResponseHelper::apiError('La marca de vehículo no existe', 'No existe el id: '. $id ,404, 'GET_VEHICLE_BRAND_ERROR');
            }

            // Retornar la marca de vehículo
            return ApiResponseHelper::apiSuccess(200, 'Marca de vehículo encontrada', $vehicleBrand);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener la marca del vehículo', $e->getMessage(), 500, 'GET_VEHICLE_BRAND_ERROR');
        }
    }

    /**
     * Actualizar una marca de vehículo específica por ID en la base de datos.
     *
     * @param  \App\Http\Requests\Users\UpdateVehicleBrandRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateVehicleBrandRequest $request, $id)
    {
        // La marca ya ha sido encontrado en UpdateVehicleBrandRequest
        $vehicleBrand = $request->vehicleBrandModel;

        // Validar los datos recibidos
        $data = $request->validated();

        // Actualizar la marca con los datos validados
        $vehicleBrand->update($data);

        // Devolver respuesta de éxito
        return ApiResponseHelper::apiSuccess(200, 'Marca de vehículo actualizada exitosamente', $vehicleBrand);
    }

    /**
     * Eliminar una marca de vehículo específica por ID.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // Buscar la marca del vehículo por ID
            $vehicleBrand = VehicleBrand::find($id);

            if (!$vehicleBrand) {
                // Retornar respuesta de error si la marca del vehículo no se encuentra
                return ApiResponseHelper::apiError('Marca de vehículo no encontrada', null, 404, 'VEHICLE_BRAND_NOT_FOUND');
            }

            // Eliminar la marca del vehículo
            $vehicleBrand->delete();
            return ApiResponseHelper::apiSuccess(200, 'Marca de vehículo eliminada exitosamente');

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Hubo un error al eliminar la marca de vehículo', $e->getMessage(), 500, 'DELETE_VEHICLE_BRAND_ERROR');
        }
    }
}
