<?php

namespace App\Http\Controllers\Acquisitions;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Acquisitions\AttatchAcquisitionBatchRequest;
use App\Http\Requests\Acquisitions\AttatchAcquisitionRequest;
use App\Http\Requests\Valuations\ChecklistValuationRequest;
use App\Http\Requests\Valuations\DetailValuationRequest;
use App\Http\Requests\Valuations\DownloadValuationRequest;
use App\Http\Requests\Valuations\UploadDocumentationRequest;
use App\Models\Valuations\AcquisitionCheckpoint;
use App\Models\Valuations\AcquisitionCheckpointValue;
use App\Models\Valuations\VehicleValuation;
use App\Services\ValuationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class AcquisitionController extends Controller
{

    protected $valuationService;

    public function __construct(ValuationService $valuationService)
    {
        $this->valuationService = $valuationService;

    }

    /**
     * Mostrar valuación mediante uuid.
     * 
     * @param  \App\Http\Requests\Users\DetailValuationRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail( DetailValuationRequest $request)
    {
        try {
            
            $data = $request->validated();
            
            $valuation = VehicleValuation::findByUuid($data['valuation_uuid'], ['vehicle.brand', 'vehicle.line', 'vehicle.model', 'vehicle.version', 'vehicle.body', 'vehicle.specification','dealership', 'appointment.vehicle', 'appointment.customer', 'technician.userProfile', 'spareParts.partSupplierOriginal', 'repairs']);

            if($valuation){
                return ApiResponseHelper::apiSuccess(200, 'Valuacion encontrada', $valuation);
            } else {
                return ApiResponseHelper::apiError('La valuación no existe', 'No existe el el uuid: '. $data['valuation_uuid'] ,404, 'RECOVER_VALUATION_ERROR');
            }
        
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la valuacion', $e->getMessage(), 500, 'GET_VALUATION_ERROR');
        }
    }


    /**
     * Encontrar checklist de la adquisición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checklist( ChecklistValuationRequest $request)
    {
        try {

            $data = $request->validated();

            $valuation = VehicleValuation::findByUuid($data['valuation_uuid']);

            $checklist = $valuation->acquisition_checkpoints()
                ->when(!empty($data['section_name']), function ($query) use ($data) {
                    return $query->where('section_name', $data['section_name']);
                })
                ->orderBy('id','asc')
                ->get();

            $response = $checklist->map(function($checkpoint) {
                return [
                    'uuid' => $checkpoint->uuid,
                    'name' => $checkpoint->name,
                    'description' => $checkpoint->description,
                    'values' => $checkpoint->values,
                    'value_type' => $checkpoint->value_type,
                    'section_name' => $checkpoint->section_name,
                    'image_path' => $checkpoint->image_path,
                    'selected_value' => $checkpoint->selected_value,
                    'created_at' => $checkpoint->created_at,
                ];
            });

            return ApiResponseHelper::apiSuccess(200, 'Checklist obtenida exitosamente', $response);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el checklist', $e->getMessage(), 500, 'GET_CHECKLIST_ERROR');
        }
    }

    /**
     * Actualizar checkpoint
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function attatch(AttatchAcquisitionRequest $request)
    {
        try {
            
            $data = $request->validated();

            $valuation = VehicleValuation::findByUuid($data['valuation_uuid']);

            $checkpoint = AcquisitionCheckpoint::findByUuid($data['checkpoint_uuid']);

            if (!$valuation || !$checkpoint) {

                return ApiResponseHelper::apiError('No se encontró la información solicitada', 'No existe algun uuid: '. $data['valuation_uuid'] .' '.$data['checkpoint_uuid'] ,404, 'GET_VALUATION_CHEKPOINT_ERROR');
            }

            $checkpoint_value = AcquisitionCheckpointValue::firstOrCreate(['valuation_id' => $valuation->id, 'checkpoint_id' => $checkpoint->id]);

            $checkpoint_value->update(['selected_value' => $data['selected_value']]);

            $status = $this->valuationService->statusAcquisition($valuation);

            if( $status ){

                $valuation->update(['status_acquisition' => 'acquisition_ready']);
            }

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Punto de documentacion asociado correctamente a la valuacion:');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al vincular la valuacion con el checkpoint', $e->getMessage(), 500, 'CHECKPOINT_ACQUISITION_ATTATCH_ERROR');
        }
    }


    public function uploadPDF( UploadDocumentationRequest $request){

        try{

            $base_folder = env('AWS_DOCUMENTATION_FOLDER_BASE', 'default_folder');
            $aws_url = env('AWS_CLOUDFRONT_URL');


            $data = $request->validated();

            $valuation = VehicleValuation::findByUuid($data['valuation_uuid']);

            $file = $request->file('documentation_pdf');

            $s3_path = $base_folder . '/' . $valuation->uuid . '/' . time(). '_'. '.' . $file->getClientOriginalExtension();
                        
            $s3_result = Storage::disk('s3')->put($s3_path, file_get_contents($file));

            if ($s3_result) {
                
                // $file_url = Storage::disk('s3')->url($s3_path);

                $valuation->update(['acquisition_pdf' => ($aws_url . '/' . $s3_path), 'status' => 'complete_file']);
            
            } else {

                return ApiResponseHelper::apiError('Error al subir el pdf a S3', null, 500, 'UPLOAD_VALUATION_ERROR');
            
            }

            return ApiResponseHelper::apiSuccess(200, 'PDF subido exitosamente', null );

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al subir el pdf de la valuacion', $e->getMessage(), 500, 'UPLOAD_VALUATION_ERROR');
        }

    }

}
