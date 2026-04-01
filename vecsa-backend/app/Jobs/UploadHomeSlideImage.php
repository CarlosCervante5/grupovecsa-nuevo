<?php

namespace App\Jobs;

use App\Helpers\ApiResponseHelper;
use App\Models\HomeSlide;
use Cloudinary\Cloudinary;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadHomeSlideImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $slide_uuid;
    protected $image_type;
    protected $original_filename;
    public $tries = 5;
    public $backoff = 60;
    protected $base_folder;
    protected $aws_url;

    /**
     * Create a new job instance.
     */
    public function __construct(String $path, String $slide_uuid, String $image_type, String $original_filename)
    {
        $this->path = $path;
        $this->slide_uuid = $slide_uuid;
        $this->image_type = $image_type;
        $this->original_filename = $original_filename;
        $this->base_folder = env('CLOUDINARY_HOME_SLIDES_FOLDER_BASE', 'default_folder');
        $this->aws_url = env('AWS_CLOUDFRONT_URL');
    }

    /**
     * Execute the job.
     */
    public function handle(Cloudinary $cloudinary): void
    {
        $this->validateInputs();

        try {
            $slide = HomeSlide::findByUuid($this->slide_uuid);

            if (!$slide) {
                Log::error('HomeSlide not found for UUID: ' . $this->slide_uuid);
                return;
            }

            Log::info('UploadHomeSlideImage job details:', [
                'slide_uuid' => $slide->uuid,
                'path' => $this->path,
                'image_type' => $this->image_type
            ]);

            $name = time() . '_' . $this->image_type;

            // Sube la imagen a Cloudinary
            $cloudinary_file = $cloudinary->uploadApi()->upload(storage_path('app/' . $this->path), [
                'public_id' => $name,
                'folder' => $this->base_folder . '/' . $slide->uuid,
                'transformation' => [
                    'quality' => 'auto',
                    'fetch_format' => 'jpg'
                ]
            ]);

            $s3_path = $this->base_folder . '/' . $slide->uuid . '/' . $name . '.jpg';

            $image_contents = file_get_contents($cloudinary_file['secure_url']);

            $s3_result = Storage::disk('s3')->put($s3_path, $image_contents);

            if ($s3_result) {
                // Actualiza el campo correspondiente según el tipo de imagen
                $field = $this->image_type === 'desktop' ? 'desktop_image_path' : 'mobile_image_path';
                $slide->update([
                    $field => $this->aws_url . '/' . $s3_path
                ]);
            } else {
                throw new Exception('Failed to upload image to S3');
            }

            Log::info('HomeSlide image uploaded successfully:', [
                'slide_uuid' => $slide->uuid,
                'image_type' => $this->image_type,
                'image_path' => $this->aws_url . '/' . $s3_path
            ]);

            $cloudinary->uploadApi()->destroy($cloudinary_file['public_id']);

            Storage::delete($this->path);

            ApiResponseHelper::imageSuccess(200, 'Imagen de slide subida correctamente al servicio externo', ['url' => $this->aws_url . '/' . $s3_path]);

        } catch (Exception $e) {
            Log::error('Error uploading home slide image:', ['exception' => $e->getMessage()]);
            ApiResponseHelper::imageError('Error en el job para subir la imagen del slide: ' . $this->slide_uuid, $e->getMessage(), 500, 'UPLOAD_IMAGE_ERROR');
        }
    }

    /**
     * Validates the required inputs.
     */
    protected function validateInputs(): void
    {
        $requiredFields = [
            'path' => $this->path,
            'slide_uuid' => $this->slide_uuid,
            'image_type' => $this->image_type,
            'original_filename' => $this->original_filename
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                throw new Exception("{$field} is required");
            }
        }
    }
}
