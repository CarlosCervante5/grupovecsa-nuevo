<?php

namespace App\Jobs;

use App\Helpers\ApiResponseHelper;
use App\Models\EventMultimedia;
use App\Models\MarketingEvent;
use Cloudinary\Cloudinary;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadEventMultimedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $event_uuid;
    protected $sort_id;
    protected $original_filename;
    protected $mimeType;
    public $tries = 5;
    public $backoff = 60;
    protected $base_folder;
    protected $aws_url;

    /**
     * Create a new job instance.
     */
    public function __construct( String $path, String $event_uuid, int $index, String $original_filename, String $mimeType)
    {
        $this->path = $path;
        $this->event_uuid = $event_uuid;
        $this->sort_id = $index;
        $this->original_filename = $original_filename;
        $this->mimeType = $mimeType;
        $this->base_folder = env('AWS_EVENT_MULTIMEDIA_FOLDER_BASE', 'default_folder');
        $this->aws_url = env('AWS_CLOUDFRONT_URL');
    }

    /**
     * Execute the job.
     */
    public function handle(Cloudinary $cloudinary): void
    {

        $this->validateInputs();

        try {
            // Encuentra la campaña por UUID
            $event = MarketingEvent::findByUuid($this->event_uuid);
            
            if (!$event) {
                Log::error('Event not found for UUID: ' . $this->event_uuid);
                return;
            }
    
            Log::info('Job details:', [
                'event_id' => $event->id,
                'event_uuid' => $event->uuid,
                'path' => $this->path,
                'sort_id' => $this->sort_id
            ]);
    
            $name = time() . '_' . $this->sort_id;
    
            // Sube el multimedia a Cloudinary
            if (strstr($this->mimeType, 'image/')) {
                $cloudinary_file = $cloudinary->uploadApi()->upload(storage_path('app/' . $this->path), [
                    'public_id' => $name,
                    'folder' => $this->base_folder . '/' . $event->uuid,
                    'transformation' => [
                        'quality' => 'auto',
                        'fetch_format' => 'jpg'
                    ]
                ]);
            } elseif (strstr($this->mimeType, 'video/')) {
                $cloudinary_file = $cloudinary->uploadApi()->upload(storage_path('app/' . $this->path), [
                    'resource_type' => 'video',
                    'public_id' => $name,
                    'folder' => $this->base_folder . '/' . $event->uuid,
                    'transformation' => [
                        'quality' => 'auto',
                        'fetch_format' => 'auto',
                    ]
                ]);
            } else {
                throw new Exception('Unsupported file type');
            }

            $s3_path = $this->base_folder . '/' . $event->uuid . '/' . $name . '.jpg';
            
            $image_contents = file_get_contents($cloudinary_file['secure_url']);
            
            $s3_result = Storage::disk('s3')->put($s3_path, $image_contents);

            if ($s3_result) {

                $multimedia = EventMultimedia::create([
                    'event_id' => $event->id,
                    'sort_id' => $this->sort_id,
                    'multimedia_path' => $this->aws_url . '/' . $s3_path
                ]);
            
            } else {
                throw new Exception('Failed to upload image to S3');
            }

            $cloudinary->uploadApi()->destroy($cloudinary_file['public_id']);

            Storage::delete($this->path);

            Log::info('Multimedia associated successfully:', [
                'multimedia_id' => $multimedia->id,
                'event_id' => $event->id,
                'multimedia_path' => $multimedia->multimedia_path
            ]);
       
            Storage::delete($this->path);
    
            ApiResponseHelper::imageSuccess(200, 'Multimedia subida correctamente al servicio externo', ['url' => $this->aws_url . '/' . $s3_path]);
    
        } catch (Exception $e) {
            // Manejo de excepciones
            Log::error('Error uploading image:', ['exception' => $e->getMessage()]);
            ApiResponseHelper::imageError('Error en el job para subir el multimedia para id: ' . $this->event_uuid, $e->getMessage(), 500, 'UPLOAD_MULTIMEDIA_ERROR');
        }
    }

    /**
     * Validates the required inputs.
     */
    protected function validateInputs(): void
    {
        $requiredFields = [
            'path' => $this->path,
            'event_uuid' => $this->event_uuid,
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
