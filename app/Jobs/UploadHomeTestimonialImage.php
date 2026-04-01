<?php

namespace App\Jobs;

use App\Helpers\ApiResponseHelper;
use App\Models\HomeTestimonial;
use Cloudinary\Cloudinary;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadHomeTestimonialImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $testimonial_uuid;
    protected $sort_id;
    protected $original_filename;
    public $tries = 5;
    public $backoff = 60;
    protected $base_folder;
    protected $aws_url;

    /**
     * Create a new job instance.
     */
    public function __construct(String $path, String $testimonial_uuid, int $sort_id, String $original_filename)
    {
        $this->path = $path;
        $this->testimonial_uuid = $testimonial_uuid;
        $this->sort_id = $sort_id;
        $this->original_filename = $original_filename;
        $this->base_folder = env('CLOUDINARY_HOME_TESTIMONIALS_FOLDER_BASE', 'default_folder');
        $this->aws_url = env('AWS_CLOUDFRONT_URL');
    }

    /**
     * Execute the job.
     */
    public function handle(Cloudinary $cloudinary): void
    {
        $this->validateInputs();

        try {
            $testimonial = HomeTestimonial::findByUuid($this->testimonial_uuid);

            if (!$testimonial) {
                Log::error('HomeTestimonial not found for UUID: ' . $this->testimonial_uuid);
                return;
            }

            Log::info('UploadHomeTestimonialImage job details:', [
                'testimonial_uuid' => $testimonial->uuid,
                'path' => $this->path,
                'sort_id' => $this->sort_id
            ]);

            $name = time() . '_' . $this->sort_id;

            // Sube la imagen a Cloudinary
            $cloudinary_file = $cloudinary->uploadApi()->upload(storage_path('app/' . $this->path), [
                'public_id' => $name,
                'folder' => $this->base_folder . '/' . $testimonial->uuid,
                'transformation' => [
                    'quality' => 'auto',
                    'fetch_format' => 'jpg'
                ]
            ]);

            $s3_path = $this->base_folder . '/' . $testimonial->uuid . '/' . $name . '.jpg';

            $image_contents = file_get_contents($cloudinary_file['secure_url']);

            $s3_result = Storage::disk('s3')->put($s3_path, $image_contents);

            if ($s3_result) {
                // Actualiza el campo image_path del testimonio
                $testimonial->update([
                    'image_path' => $this->aws_url . '/' . $s3_path
                ]);
            } else {
                throw new Exception('Failed to upload image to S3');
            }

            Log::info('HomeTestimonial image uploaded successfully:', [
                'testimonial_uuid' => $testimonial->uuid,
                'image_path' => $this->aws_url . '/' . $s3_path
            ]);

            $cloudinary->uploadApi()->destroy($cloudinary_file['public_id']);

            Storage::delete($this->path);

            ApiResponseHelper::imageSuccess(200, 'Imagen de testimonio subida correctamente al servicio externo', ['url' => $this->aws_url . '/' . $s3_path]);

        } catch (Exception $e) {
            Log::error('Error uploading home testimonial image:', ['exception' => $e->getMessage()]);
            ApiResponseHelper::imageError('Error en el job para subir la imagen del testimonio: ' . $this->testimonial_uuid, $e->getMessage(), 500, 'UPLOAD_IMAGE_ERROR');
        }
    }

    /**
     * Validates the required inputs.
     */
    protected function validateInputs(): void
    {
        $requiredFields = [
            'path' => $this->path,
            'testimonial_uuid' => $this->testimonial_uuid,
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
