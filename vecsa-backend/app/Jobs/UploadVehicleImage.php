<?php

namespace App\Jobs;

use App\Helpers\ApiResponseHelper;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Cloudinary\Cloudinary;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadVehicleImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $vehicle_id;
    protected $vehicle_uuid;
    protected $sort_id;
    protected $original_filename;
    protected $is_last;
    public $tries = 5;
    public $backoff = 60;
    protected $base_folder;
    protected $aws_url;

    /**
     * Create a new job instance.
     */
    public function __construct( String $path, String $vehicle_uuid, int $vehicle_id, int $index, String $original_filename, bool $is_last)
    {
        $this->path = $path;
        $this->vehicle_uuid = $vehicle_uuid;
        $this->vehicle_id = $vehicle_id;
        $this->sort_id = $index;
        $this->original_filename = $original_filename;
        $this->is_last = $is_last;
        $this->base_folder = env('AWS_VEHICLES_FOLDER_BASE', 'default_folder');
        $this->aws_url = env('AWS_CLOUDFRONT_URL');
        
    }

    /**
     * Execute the job.
     */
    public function handle(Cloudinary $cloudinary): void
    {   
        // Validaciones
        $this->validateInputs();

        try {

            Log::info('Uploading image for vehicle', [
                'vehicle_id' => $this->vehicle_id,
                'vehicle_uuid' => $this->vehicle_uuid,
                'path' => $this->path,
                'sort_id' => $this->sort_id
            ]);

            $name = time().'_'.$this->sort_id;

            $cloudinary_file = $cloudinary->uploadApi()->upload(storage_path('app/' . $this->path), [
                'public_id' => $name,
                'folder' => $this->base_folder . '/' . $this->vehicle_uuid,
                'transformation' => [
                    'quality' => 'auto',
                    'fetch_format' => 'jpg'
                ]
            ]);

            $s3_path = $this->base_folder . '/' . $this->vehicle_uuid . '/' . $name . '.jpg';
            
            $image_contents = file_get_contents($cloudinary_file['secure_url']);
            
            $s3_result = Storage::disk('s3')->put($s3_path, $image_contents);

            if ($s3_result) {
                
                VehicleImage::create([
                    'sort_id' => $this->sort_id,
                    'image_name' => $this->original_filename,
                    'vehicle_id' => $this->vehicle_id,
                    'service_image_url' =>  $this->aws_url . '/' . $s3_path
                ]);

                if ($this->is_last) {
                    Vehicle::where('id', $this->vehicle_id)
                        ->update(['page_status' => 'active']);
                }
            
            } else {
                throw new Exception('Failed to upload image to S3');
            }

            $cloudinary->uploadApi()->destroy($cloudinary_file['public_id']);

            Storage::delete($this->path);

            ApiResponseHelper::imageSuccess(200, 'Imagen subida correctamente al servicio externo', ['url' => $this->aws_url . '/' . $s3_path]); 

        } catch (\Exception $e) {
            ApiResponseHelper::imageError('Error en el job para subir la imagen para id: '.$this->vehicle_id, $e->getMessage(), 500, 'UPLOAD_IMAGE_ERROR');

            ApiResponseHelper::imageError('Imagen guardada localmente para vehículo uuid: '.$this->vehicle_uuid, 'Guardada en: ' . $this->path, 500, 'SAVE_LOCAL_IMAGE_ERROR');
        }
    }

    /**
     * Validates the required inputs.
     */
    protected function validateInputs(): void
    {
        $requiredFields = [
            'path' => $this->path,
            'vehicle_uuid' => $this->vehicle_uuid,
            'vehicle_id' => $this->vehicle_id,
            'sort_id' => $this->sort_id,
            'original_filename' => $this->original_filename
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                throw new Exception("{$field} is required");
            }
        }
    }
}
