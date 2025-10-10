<?php

namespace App\Jobs;

use App\Helpers\ApiResponseHelper;
use App\Models\VehicleImage;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UpdateVehicleImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $vehicle_image;
    protected $vehicle_uuid;

    public $tries = 5;
    public $backoff = 60;
    protected $base_folder;
    protected $aws_url;

    /**
     * Create a new job instance.
     */
    public function __construct(VehicleImage $vehicle_image, String $vehicle_uuid)
    {
        $this->vehicle_uuid = $vehicle_uuid;
        $this->vehicle_image = $vehicle_image;
        $this->base_folder = env('AWS_VEHICLES_FOLDER_BASE', 'default_folder');
        $this->aws_url = env('AWS_CLOUDFRONT_URL');
        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {   
        // Validaciones
        $this->validateInputs();

        try {

            Log::info('Uploading image for vehicle image', [
                'vehicle_image' => $this->vehicle_image->uuid,
            ]);

            $name = time().'_'.$this->vehicle_image->sort_id;

            $s3_path = $this->base_folder . '/' . $this->vehicle_uuid . '/' . $name . '.jpg';
            
            $image_contents = file_get_contents($this->vehicle_image->service_image_url);
            
            $s3_result = Storage::disk('s3')->put($s3_path, $image_contents);

            if ($s3_result) {
                
                $this->vehicle_image->update([
                    'service_image_url' =>  $this->aws_url . '/' . $s3_path
                ]);
            
            } else {
                throw new Exception('Failed to upload image to S3');
            }

            ApiResponseHelper::imageSuccess(200, 'Imagen subida correctamente al servicio externo', ['url' => $this->aws_url . '/' . $s3_path]); 

        } catch (\Exception $e) {
            ApiResponseHelper::imageError('Error en el job para actualizar la imagen para id: '.$this->vehicle_image->uuid, $e->getMessage(), 500, 'UPDATE_IMAGE_ERROR');
        }
    }

    /**
     * Validates the required inputs.
     */
    protected function validateInputs(): void
    {
        $requiredFields = [
            'vehicle_uuid' => $this->vehicle_uuid,
            'vehicle_image' => $this->vehicle_image,
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                throw new Exception("{$field} is required");
            }
        }
    }
}
