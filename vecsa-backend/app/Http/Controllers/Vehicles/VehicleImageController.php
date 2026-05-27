<?php

namespace App\Http\Controllers\Vehicles;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Files\UploadVehicleImageRequest;
use App\Http\Requests\Vehicles\VehicleImages\DeleteVehicleImageBatchRequest;
use App\Http\Requests\Vehicles\VehicleImages\DeleteVehicleImageRequest;
use App\Http\Requests\Vehicles\VehicleImages\UpdateSortVehicleImageRequest;
use App\Http\Requests\Vehicles\VehicleImages\UpdateVehicleImageStorageRequest;
use App\Jobs\UpdateVehicleImage;
use App\Jobs\UploadVehicleImage;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleImageController extends Controller
{
    /**
     * Crear nuevo set de imágenes de vehículo.
     *
     * @param  \App\Http\Requests\Users\UploadVehicleImageRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UploadVehicleImageRequest $request)
    {
        try {
            
            $vehicle_uuid = $request->input('vehicle_uuid');
            $images = $request->file('images');

            $vehicle = Vehicle::findByUuid($vehicle_uuid);

            if (!$vehicle) {

                return ApiResponseHelper::apiError('El vehiculo no existe', 'No existe el id: '. $vehicle_uuid ,404, 'CREATE_VEHICLE_IMAGES_ERROR');
            }

            $jobs_in_queue = DB::table('jobs')
                ->where('payload', 'like', '%'.$vehicle_uuid.'%')
                ->exists();
            
            if ($jobs_in_queue) {
                return ApiResponseHelper::apiError('Ya hay una carga de imágenes en progreso para este vehículo. Por favor espere a que se complete.', null, 429, 'IMAGE_UPLOAD_IN_PROGRESS');
            }

            // Obtener el sort_id más alto de las imágenes del vehículo, sino regresa 1
            $sort_id = $vehicle->images->max('sort_id') + 1 ?? 1;

            $invalidImages = [];

            foreach ($images as $index => $image) {

                // Validar si el archivo es válido
                if (!$image->isValid()) {
                    // Registrar la imagen inválida
                    $invalidImages[] = $image->getClientOriginalName();
                    continue; // Saltar a la siguiente iteración del bucle
                }

                // Guardar temporalmente el archivo
                $path = \App\Support\UploadableImage::storeTemp($image);


                // Determinar si es la última imagen
                $is_last = $index === count($images) - 1;

                // Enviar cada lote a una cola de trabajo para procesamiento en segundo plano
                UploadVehicleImage::dispatch($path, $vehicle->uuid, $vehicle->id, ($sort_id + $index), $image->getClientOriginalName(), $is_last);
            }

            if (!empty($invalidImages)) {
                return ApiResponseHelper::apiSuccess(201, 'El set de imagenes se envió a procesar, pero contenía ALGUNOS archivos inválidos o corruptos', $invalidImages);
            }
        
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Set de imagenes enviadas a procesar');

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al crear el set de imagenes', $e->getMessage(), 500, 'CREATE_VEHICLE_IMAGES_ERROR');
        }
    }

    /**
     * Actualizar orden de las imágenes
     *
     * @param  \App\Http\Requests\Vehicles\VehicleImages\UpdateSortVehicleImageRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sortUpdate( UpdateSortVehicleImageRequest $request)
    {
        try {

            $data = $request->validated();

            DB::transaction(function () use ($data) {
                foreach ($data['image_order'] as $order) {
                    VehicleImage::where('uuid', $order['uuid'])->update(['sort_id' => $order['sort_id']]);
                }
            });

            return ApiResponseHelper::apiSuccess(200, 'Imagenes reordenadas exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la imagen vehículo', $e->getMessage(), 500, 'GET_VEHICLE_IMAGE_ERROR');
        }
    }

    /**
     * Borrar imagen del vehículo
     *
     * @param  \App\Http\Requests\Vehicles\VehicleImages\DeleteVehicleImageRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete( DeleteVehicleImageRequest $request)
    {
        try {

            $data = $request->validated();

            $vehicle_image = VehicleImage::findByUuid($data['uuid']);
        
            if ($vehicle_image) {
                
                $vehicle = $vehicle_image->vehicle()->first();

                $vehicle_image->delete();

                $vehicle_images = $vehicle->images()->count();

                if ($vehicle_images === 0) {
                    $vehicle->update(['page_status' => 'inactive']);
                }

                return ApiResponseHelper::apiSuccess(200, 'Imagen de vehículo eliminada exitosamente');
                
            } else {
                return ApiResponseHelper::apiError('La imagen del vehículo', 'No existe el id: '. $data['uuid'] ,404, 'GET_VEHICLE_IMAGE_ERROR');
            }


        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la imagen vehículo', $e->getMessage(), 500, 'GET_VEHICLE_IMAGE_ERROR');
        }
    }

    /**
     * Borrar imagenes del vehículo
     *
     * @param  \App\Http\Requests\Vehicles\VehicleImages\DeleteVehicleImageBatchRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBatch( DeleteVehicleImageBatchRequest $request)
    {
        try {

            $data = $request->validated();

            $vehicle = Vehicle::findByUuid($data['vehicle_uuid']);

            if (!$vehicle) {

                return ApiResponseHelper::apiError('El vehiculo no existe', 'No existe el uuid: '. $data['vehicle_uuid'] ,404, 'CREATE_VEHICLE_IMAGES_ERROR');
            }
        
            $vehicle->images()->delete();

            $vehicle->update(['page_status' => 'inactive']);

            return ApiResponseHelper::apiSuccess(200, 'Imagenes de vehículo eliminadas exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar las imagenes del vehiculo', $e->getMessage(), 500, 'DELETE_VEHICLE_IMAGES_ERROR');
        }
    }


    /**
     * Subir imagenes de un vehículo a AWS
     *
     * @param  \App\Http\Requests\Users\UploadVehicleImageRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStorage(UpdateVehicleImageStorageRequest $request)
    {
        try {
            
            $vehicle_uuid = $request->input('vehicle_uuid');

            $vehicle = Vehicle::findByUuid($vehicle_uuid);

            if (!$vehicle) {

                return ApiResponseHelper::apiError('El vehiculo no existe', 'No existe el id: '. $vehicle_uuid ,404, 'CREATE_VEHICLE_IMAGES_ERROR');
            }

            $jobs_in_queue = DB::table('jobs')
                ->where('payload', 'like', '%'.$vehicle_uuid.'%')
                ->exists();
            
            if ($jobs_in_queue) {
                return ApiResponseHelper::apiError('Ya hay una carga de imágenes en progreso para este vehículo. Por favor espere a que se complete.', null, 429, 'IMAGE_UPLOAD_IN_PROGRESS');
            }

            // Obtener imagenes
            $vehicle_images = $vehicle->images;

            foreach ($vehicle_images as $image) {

                UpdateVehicleImage::dispatch($image, $vehicle->uuid);
            }
        
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Set de imagenes enviadas a procesar');

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al actualziar el set de imagenes', $e->getMessage(), 500, 'UPDATE_VEHICLE_IMAGES_ERROR');
        }
    }

}
