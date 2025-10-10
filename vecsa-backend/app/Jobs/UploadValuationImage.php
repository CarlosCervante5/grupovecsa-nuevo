<?php

namespace App\Jobs;

use App\Helpers\ApiResponseHelper;
use App\Models\Valuations\ValuationImage;
use Cloudinary\Cloudinary;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadValuationImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $valuation_id;
    protected $valuation_uuid;
    protected $sort_id;
    protected $original_filename;
    protected $name;
    protected $group_name;
    public $tries = 5;
    public $backoff = 60;
    protected $base_folder;
    protected $aws_url;

    /**
     * Create a new job instance.
     */
    public function __construct( String $path, String $valuation_uuid, int $valuation_id, int $index, String $original_filename, String $name, String $group_name)
    {
        $this->path = $path;
        $this->valuation_uuid = $valuation_uuid;
        $this->valuation_id = $valuation_id;
        $this->sort_id = $index;
        $this->original_filename = $original_filename;
        $this->name = $name;
        $this->group_name = $group_name;
        $this->base_folder = env('AWS_VALUATIONS_FOLDER_BASE', 'default_folder');
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
                'valuation_id' => $this->valuation_id,
                'valuation_uuid' => $this->valuation_uuid,
                'path' => $this->path,
                'sort_id' => $this->sort_id
            ]);

            $name = time().'_'.$this->sort_id;

            $cloudinary_file = $cloudinary->uploadApi()->upload(storage_path('app/' . $this->path), [
                'public_id' => $name,
                'folder' => $this->base_folder . '/' . $this->valuation_uuid,
                'transformation' => [
                    'quality' => 'auto',
                    'fetch_format' => 'jpg'
                ]
            ]);

            $s3_path = $this->base_folder . '/' . $this->valuation_uuid . '/' . $name . '.jpg';
            
            $image_contents = file_get_contents($cloudinary_file['secure_url']);
            
            $s3_result = Storage::disk('s3')->put($s3_path, $image_contents);

            if ($s3_result) {

                if ( $this->group_name == 'interior' || $this->group_name == 'exterior' ){

                    ValuationImage::where('name', $this->name)
                    ->where('group_name', $this->group_name)
                    ->where('valuation_id', $this->valuation_id)
                    ->delete();

                }

                ValuationImage::create([
                    'sort_id' => $this->sort_id,
                    'name' => $this->name,
                    'group_name' => $this->group_name,
                    'image_path' => $this->aws_url . '/' . $s3_path,
                    'valuation_id' => $this->valuation_id,
                ]);

            } else {
                throw new Exception('Failed to upload image to S3');
            }

            $cloudinary->uploadApi()->destroy($cloudinary_file['public_id']);

            Storage::delete($this->path);

            ApiResponseHelper::imageSuccess(200, 'Imagen subida correctamente al servicio externo.', ['url' => $this->aws_url . '/' . $s3_path]);

        } catch (\Exception $e) {
            ApiResponseHelper::imageError('Error en el job para subir la imagen para id: '.$this->valuation_id, $e->getMessage(), 500, 'UPLOAD_IMAGE_ERROR');

            ApiResponseHelper::imageError('Imagen guardada localmente para valuacion uuid: '.$this->valuation_uuid, 'Guardada en: ' . $this->path, 500, 'SAVE_LOCAL_IMAGE_ERROR');
        }
    }

    /**
     * Validates the required inputs.
     */
    protected function validateInputs(): void
    {
        $requiredFields = [
            'path' => $this->path,
            'valuation_uuid' => $this->valuation_uuid,
            'valuation_id' => $this->valuation_id,
            'sort_id' => $this->sort_id,
            'original_filename' => $this->original_filename,
            'name' => $this->name,
            'group_name' => $this->group_name
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                throw new Exception("{$field} is required");
            }
        }
    }
}
