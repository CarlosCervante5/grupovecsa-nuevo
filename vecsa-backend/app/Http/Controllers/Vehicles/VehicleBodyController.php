<?php

namespace App\Http\Controllers\Vehicles;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicles\Bodies\StoreVehicleBodyRequest;
use App\Http\Requests\Vehicles\Bodies\UpdateVehicleBodyRequest;
use App\Models\VehicleBody;
use Illuminate\Validation\ValidationException;

class VehicleBodyController extends Controller
{
    /**
     * Obtener una lista de todos las carrocerías de vehículos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Obtener todos las carrocerías de vehículos
            $vehicleBodies = VehicleBody::all();

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(200, 'Carrocerias de vehículos obtenidos exitosamente', ['vehicle_bodies' => $vehicleBodies]);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener la lista de carrocerías de vehículos', $e->getMessage(), 500, 'GET_VEHICLE_BODY_ERROR');
        }
    }

    /**
     * Crear una nueva carrocería de vehículos.
     *
     * @param  \App\Http\Requests\Users\StoreVehicleBodyRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreVehicleBodyRequest $request)
    {
        try {
            // Validar los datos recibidos
            $data = $request->validated();

            // Crear una nueva carrocería de vehículos
            $vehicleBody = VehicleBody::create($data);

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Carrocería de vehículo creada exitosamente', $vehicleBody);

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al crear la carrocería de vehículo', $e->getMessage(), 500, 'CREATE_VEHICLE_BODY_ERROR');
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

            $vehicleBody = VehicleBody::where('id', $id)->first();
        
            if (!$vehicleBody) {

                return ApiResponseHelper::apiError('La carrocería no existe', 'No existe el id: '. $id ,404, 'GET_VEHICLE_BODY_ERROR');
            }

            // Retornar la carrocería de vehículo encontrada
            return ApiResponseHelper::apiSuccess(200, 'Carrocería de vehículo encontrada', $vehicleBody);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener la carrocería de vehículo', $e->getMessage(), 500, 'GET_VEHICLE_BODY_ERROR');
        }
    }

    /**
     * Actualizar la carrocería específica por ID en la base de datos.
     *
     * @param  \App\Http\Requests\Users\UpdateVehicleBodyRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateVehicleBodyRequest $request, $id)
    {
        // La marca ya ha sido encontrado en UpdateVehicleBodyRequest
        $vehicleBody = $request->vehicleBodyModel;

        // Validar los datos recibidos
        $data = $request->validated();

        // Actualizar la marca con los datos validados
        $vehicleBody->update($data);

        // Devolver respuesta de éxito
        return ApiResponseHelper::apiSuccess(200, 'Carrocería de vehículo actualizada exitosamente', $vehicleBody);
    }

    /**
     * Eliminar la carrocería específica por ID.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // Buscar la carrocería por ID
            $vehicleBody = VehicleBody::find($id);

            if (!$vehicleBody) {
                // Retornar respuesta de error si la carrocería no se encuentra
                return ApiResponseHelper::apiError('Carrocería de vehículo no encontrada', null, 404, 'VEHICLE_BODY_NOT_FOUND');
            }

            // Eliminar la carrocería
            $vehicleBody->delete();
            return ApiResponseHelper::apiSuccess(200, 'Carrocería de vehículo eliminada exitosamente');

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Hubo un error al eliminar la carrocería de vehículo', $e->getMessage(), 500, 'DELETE_VEHICLE_BODY_ERROR');
        }
    }
}
