<?php

namespace App\Http\Controllers\Valuations;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Files\UploadValuationImageRequest;
use App\Http\Requests\Valuations\SearchValuationImagesRequest;
use App\Jobs\UploadValuationImage;
use App\Models\Valuations\ValuationImage;
use App\Models\Valuations\VehicleValuation;
use Illuminate\Validation\ValidationException;

class ValuationImageController extends Controller
{

    protected $valuationService;

    protected $vehicleService;

    protected $userService;

    public function __construct()
    {

    }
    
    /**
     * Crear nuevo set de imágenes de valuacion.
     *
     * @param  \App\Http\Requests\Users\UploadValuationImageRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UploadValuationImageRequest $request)
    {
        try {
            
            $valuation_uuid = $request->input('valuation_uuid');
            $name = $request->input('name');
            $group_name = $request->input('group_name');
            $images = $request->file('images');

            $valuation = VehicleValuation::findByUuid($valuation_uuid);

            if (!$valuation) {

                return ApiResponseHelper::apiError('La valuacion no existe', 'No existe el id: '. $valuation ,404, 'CREATE_VALUATION_IMAGES_ERROR');
            }

            // Obtener el sort_id más alto de las imágenes del vehículo, sino regresa 1
            $sort_id = $valuation->images->max('sort_id') + 1 ?? 1;

            $invalidImages = [];

            foreach ($images as $index => $image) {

                // Validar si el archivo es válido
                if (!$image->isValid()) {
                    // Registrar la imagen inválida
                    $invalidImages[] = $image->getClientOriginalName();
                    continue; // Saltar a la siguiente iteración del bucle
                }

                // Guardar temporalmente el archivo
                $path = $image->store('temp_images');

                // Enviar cada lote a una cola de trabajo para procesamiento en segundo plano
                UploadValuationImage::dispatch($path, $valuation->uuid, $valuation->id, ($sort_id + $index), $image->getClientOriginalName(), $name, $group_name);
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
            return ApiResponseHelper::apiError('Error al crear el set de imagenes', $e->getMessage(), 500, 'CREATE_VALUATION_IMAGES_ERROR');
        }
    }


    /**
     * Buscar imágenes de valuación.
     *
     * @param  \App\Http\Requests\Users\SearchValuationImagesRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(SearchValuationImagesRequest $request){

        try {
            
            $data = $request->validated();

            $valuation = VehicleValuation::findByUuid($data['valuation_uuid']);

            if (!$valuation) {

                return ApiResponseHelper::apiError('La valuacion no existe', 'No existe el id: '. $valuation ,404, 'SEARCH_VALUATION_IMAGES_ERROR');
            }

            $images = ValuationImage::where('valuation_id', $valuation->id)->where('group_name', $data['group_name'])->get();
            
            return ApiResponseHelper::apiSuccess(200, 'Imagenes de valuación encontradas', $images);

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener el set de imagenes', $e->getMessage(), 500, 'SEARCH_VALUATION_IMAGES_ERROR');
        }
    }

    
}
