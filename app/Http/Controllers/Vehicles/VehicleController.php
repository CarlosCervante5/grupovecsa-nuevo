<?php

namespace App\Http\Controllers\Vehicles;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicles\DeleteVehicleBatchRequest;
use App\Http\Requests\Vehicles\DeleteVehicleRequest;
use App\Http\Requests\Vehicles\DetailVehicleRequest;
use App\Http\Requests\Vehicles\RandomSearchVehiclesRequest;
use App\Http\Requests\Vehicles\RestoreVehicleRequest;
use App\Http\Requests\Vehicles\SearchVehiclesRequest;
use App\Http\Requests\Vehicles\StoreVehicleRequest;
use App\Http\Requests\Vehicles\UpdateVehicleRequest;
use App\Http\Requests\Vehicles\UpdateVehicleStatusBatchRequest;
use App\Http\Requests\Vehicles\UpdateVehicleStatusRequest;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\VehiclesImport;
use App\Services\UserService;

class VehicleController extends Controller
{

    protected $vehicleService;

    public function __construct(VehicleService $vehicleService, UserService $userService)
    {
        $this->vehicleService = $vehicleService;
    }

    /**
     * Crear vehículo.
     *
     * @param  \App\Http\Requests\Users\StoreVehicleRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(StoreVehicleRequest $request)
    {
        try {

            $user = auth()->user();
            
            $data = $request->validated();

            $vehicle = $this->vehicleService->createVehicle($data, $user->id);
            
            return ApiResponseHelper::apiSuccess(201, 'Vehículo creado exitosamente', $vehicle);

        } catch (ValidationException $e) {

            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {

            return ApiResponseHelper::apiError('Error al crear el vehículo', $e->getMessage(), 500, 'CREATE_VEHICLE_ERROR');

        }
    }


    /**
     * Crear o actualizar vehículo.
     *
     * @param  \App\Http\Requests\Users\StoreVehicleRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreVehicleRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();

            $vehicle = $this->vehicleService->createOrUpdateVehicle($data, $user->id);

            return ApiResponseHelper::apiSuccess(201, 'Vehículo creado exitosamente', $vehicle);

        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear el vehículo', $e->getMessage(), 500, 'CREATE_VEHICLE_ERROR');
        }
    }

    /**
     * Mostrar vehículo mediante uuid.
     * 
     * @param  \App\Http\Requests\Users\DetailVehicleRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail( DetailVehicleRequest $request)
    {
        try {
            $data = $request->validated();
            $vehicle = Vehicle::findByUuid($data['uuid'], $data['relationship_names']);
            return ApiResponseHelper::apiSuccess(200, 'Vehículo encontrado', $vehicle);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el vehículo', $e->getMessage(), 500, 'GET_VEHICLE_ERROR');
        }
    }

    /**
     * Actualizar vehículo existente.
     *
     * @param  \App\Http\Requests\Users\UpdateVehicleRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateVehicleRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();
            
            $vehicle = $this->vehicleService->createOrUpdateVehicle($data, $user->id);
            
            return ApiResponseHelper::apiSuccess(201, 'Vehículo actualizado exitosamente', $vehicle);
        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar el vehículo', $e->getMessage(), 500, 'UPDATE_VEHICLE_ERROR');
        }
    }

    /**
     * Actualizar status vehículo existente.
     *
     * @param  \App\Http\Requests\Users\UpdateVehicleStatusRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(UpdateVehicleStatusRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();
            
            $vehicle = $this->vehicleService->updateStatus($data, $user->id);
            
            return ApiResponseHelper::apiSuccess(201, 'Estatus de vehiculo actualizado exitosamente', $vehicle);
        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar el vehículo', $e->getMessage(), 500, 'UPDATE_VEHICLE_ERROR');
        }
    }

    /**
     * Eliminar vehículo mediante uuid.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteVehicleRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();

            $deleted = $this->vehicleService->deleteVehicle($data['uuid'], $user->id);

            if ($deleted) {
                return ApiResponseHelper::apiSuccess(200, 'Vehículo eliminado exitosamente');
            } else {
                return ApiResponseHelper::apiError('El vehículo no existe', 'No existe el id: '. $data['uuid'] ,404, 'GET_VEHICLE_ERROR');
            }

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el vehículo', $e->getMessage(), 500, 'GET_VEHICLE_ERROR');
        }
    }

    /**
     * Restaurar vehículo mediante uuid.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(RestoreVehicleRequest $request)
    {
        try {

            $user = auth()->user();
            
            $data = $request->validated();

            $restored = $this->vehicleService->restoreVehicle($data['uuid'], $user->id);

            if ($restored) {
                return ApiResponseHelper::apiSuccess(200, 'Vehículo restaurado exitosamente', $restored);
            } else {
                return ApiResponseHelper::apiError('El vehículo no existe', 'No existe el vehículo con el UUID: '. $data['uuid'], 404, 'GET_VEHICLE_ERROR');
            }

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al restaurar el vehículo', $e->getMessage(), 500, 'GET_VEHICLE_ERROR');
        }
    }

    /**
     * Encontrar vehículos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(SearchVehiclesRequest $request)
    {
        try {
            $data = $request->validated();

            $vehicles = $this->vehicleService->searchVehicles($data);
            
            return ApiResponseHelper::apiSuccess(200, 'Vehículos obtenidos exitosamente', $vehicles);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la búsqueda de vehiculos', $e->getMessage(), 500, 'GET_VEHICLE_SEARCH_ERROR');
        }
    }

    /**
     * Encontrar vehículos y retornarlos en formato XML.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function preownedXML()
    {
        try {

            $vehicles = $this->vehicleService->searchVehiclesXML();
            
            return response($vehicles, 200)->header('Content-Type', 'text/xml');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la búsqueda de vehiculos', $e->getMessage(), 500, 'GET_VEHICLE_SEARCH_ERROR');
        }
    }

    /**
     * Encontrar precios máximos y mínimos de vehículos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function minMax(SearchVehiclesRequest $request)
    {
        try {
            $data = $request->validated();

            $minMax = $this->vehicleService->minMaxPrices($data);
            
            return ApiResponseHelper::apiSuccess(200, 'Precio mínimo y máximo obtenidos exitosamente', $minMax);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener min y max prices', $e->getMessage(), 500, 'GET_MIN_MAX_ERROR');
        }
    }


    /**
     * Encontrar vehículos aleatoriamente.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function randomSearch(RandomSearchVehiclesRequest $request)
    {
        try {
            $data = $request->validated();

            $vehicles = $this->vehicleService->randomSearchVehicles($data);
            
            return ApiResponseHelper::apiSuccess(200, 'Vehículos obtenidos exitosamente', $vehicles);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la búsqueda de vehiculos', $e->getMessage(), 500, 'GET_VEHICLE_SEARCH_ERROR');
        }
    }

    /**
     * Almacenar vehículos mediante un archivo csv
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function csvUpload(Request $request)
    {
        try {

            $user = auth()->user();

            $request->validate([
                'file' => 'required|mimes:csv,xlsx,xls',
            ]);

            $import = new VehiclesImport($this->vehicleService, $user->id);

            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            
            if ($failures->isNotEmpty()) {
                return ApiResponseHelper::apiSuccess(200, 'Se han subido los vehículos pero el archivo contenía algunos errores.', $failures);
            }

            return ApiResponseHelper::apiSuccess(200, 'Vehículos subidos exitosamente');


        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al subir los vehiculos', $e->getMessage(), 500, 'UPLOAD_VEHICLES_ERROR');
        }
    }

    /**
     * Eliminar vehículos por uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBatch(DeleteVehicleBatchRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();

            $deleted = $this->vehicleService->deleteVehicleBatch($data['uuids'], $user->id);

            if ($deleted) {
                return ApiResponseHelper::apiSuccess(200, 'Vehículos eliminado exitosamente');
            } else {
                return ApiResponseHelper::apiError('Los vehículos no existen', 'No existen los uuid: '. $data['uuids'] ,404, 'GET_VEHICLES_ERROR');
            }

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los vehículos', $e->getMessage(), 500, 'GET_VEHICLES_ERROR');
        }
    }

    /**
     * Eliminar vehículos por uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function inverseDeleteBatch(DeleteVehicleBatchRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();

            $deleted = $this->vehicleService->inverseDeleteVehicleBatch($data['uuids'], $user->id);

            if ($deleted) {
                return ApiResponseHelper::apiSuccess(200, 'Vehículos eliminado exitosamente');
            } else {
                return ApiResponseHelper::apiError('Los vehículos no existen', 'No existen vehiculos a eliminar: '. $data['uuids'] ,404, 'GET_VEHICLES_ERROR');
            }

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los vehículos', $e->getMessage(), 500, 'GET_VEHICLES_ERROR');
        }
    }


    /**
     * Cambiar page_status de vehículos por uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusBatch(UpdateVehicleStatusBatchRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();

            $updated = $this->vehicleService->statusVehicleBatch($data['uuids'], $data['page_status'], $user->id);

            if ($updated) {
                return ApiResponseHelper::apiSuccess(200, 'Vehículos actualizados exitosamente');
            } else {
                return ApiResponseHelper::apiError('Los vehículos no existen', 'No existen los uuid: '. $data['uuids'] ,404, 'GET_VEHICLES_ERROR');
            }

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los vehículos', $e->getMessage(), 500, 'GET_VEHICLES_ERROR');
        }
    }

}
