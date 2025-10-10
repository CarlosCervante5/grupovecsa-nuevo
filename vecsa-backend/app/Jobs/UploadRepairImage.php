<?php

namespace App\Jobs;

use App\Helpers\ApiResponseHelper;
use App\Models\Valuations\ValuationRepair;
use Cloudinary\Cloudinary;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadRepairImage
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $valuation_repair;
    protected $original_filename;
    protected $base_folder;
    protected $aws_url;

    /**
     * Create a new job instance.
     */
    public function __construct( String $path, ValuationRepair $valuation_repair, String $original_filename)
    {
        $this->path = $path;
        $this->valuation_repair = $valuation_repair;
        $this->original_filename = $original_filename;
        $this->base_folder = env('AWS_REPAIR_FOLDER_BASE', 'default_folder');
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

            Log::info('Job details:', [
                'repair_uuid' => $this->valuation_repair->uuid,
                'path' => $this->path,
            ]);

            $name = time().'_'.$this->valuation_repair->uuid;

            $cloudinary_file = $cloudinary->uploadApi()->upload(storage_path('app/' . $this->path), [
                'public_id' => $name,
                'folder' => $this->base_folder . '/' . $this->valuation_repair->uuid,
                'transformation' => [
                    'quality' => 'auto',
                    'fetch_format' => 'jpg'
                ]
            ]);

            $s3_path = $this->base_folder . '/' . $this->valuation_repair->uuid . '/' . $name . '.jpg';
            
            $image_contents = file_get_contents($cloudinary_file['secure_url']);
            
            $s3_result = Storage::disk('s3')->put($s3_path, $image_contents);

            if ($s3_result) {

                $this->valuation_repair->update(['image_path' => $this->aws_url . '/' . $s3_path]);
            
            } else {
                throw new Exception('Failed to upload image to S3');
            }

            $cloudinary->uploadApi()->destroy($cloudinary_file['public_id']);

            Storage::delete($this->path);

            ApiResponseHelper::imageSuccess(200, 'Imagen subida correctamente al servicio externo', ['url' => $this->aws_url . '/' . $s3_path]);

        } catch (\Exception $e) {
        
            ApiResponseHelper::imageError('Error en el job para subir la imagen para uuid: '.$this->valuation_repair->uuid, $e->getMessage(), 500, 'UPLOAD_IMAGE_ERROR');

            ApiResponseHelper::imageError('Imagen guardada localmente para reparacion uuid: '.$this->valuation_repair->uuid, 'Guardada en: ' . $this->path, 500, 'SAVE_LOCAL_IMAGE_ERROR');
        }
    }

    /**
     * Validates the required inputs.
     */
    protected function validateInputs(): void
    {
        $requiredFields = [
            'path' => $this->path,
            'valuation_repair' => $this->valuation_repair,
            'original_filename' => $this->original_filename
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                throw new Exception("{$field} is required");
            }
        }
    }
}
