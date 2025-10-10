<?php

namespace App\Jobs;

use App\Helpers\ApiResponseHelper;
use App\Models\MarketingCampaign;
use App\Models\MarketingPromotion;
use Cloudinary\Cloudinary;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadPromotionImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $promotion_name;
    protected $campaign_uuid;
    protected $sort_id;
    protected $original_filename;
    protected $spec_sheet;
    public $tries = 5;
    public $backoff = 60;
    protected $base_folder;
    protected $aws_url;

    /**
     * Create a new job instance.
     */
    public function __construct( String $path, String $promotion_name, String $campaign_uuid, int $index, String $original_filename, String $spec_sheet)
    {
        $this->path = $path;
        $this->promotion_name = $promotion_name;
        $this->spec_sheet = $spec_sheet;
        $this->campaign_uuid = $campaign_uuid;
        $this->sort_id = $index;
        $this->original_filename = $original_filename;
        $this->base_folder = env('AWS_PROMOTION_FOLDER_BASE', 'default_folder');
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
            // Encuentra la campaña por UUID
            $campaign = MarketingCampaign::findByUuid($this->campaign_uuid);
            
            if (!$campaign) {
                Log::error('Campaign not found for UUID: ' . $this->campaign_uuid);
                return;
            }
    
            Log::info('Job details:', [
                'campaign_id' => $campaign->id,
                'campaign_uuid' => $campaign->uuid,
                'path' => $this->path,
                'sort_id' => $this->sort_id
            ]);
    
            $name = time() . '_' . $this->sort_id;
    
            // Sube la imagen a Cloudinary
            $cloudinary_file = $cloudinary->uploadApi()->upload(storage_path('app/' . $this->path), [
                'public_id' => $name,
                'folder' => $this->base_folder . '/' . $campaign->uuid,
                'transformation' => [
                    'quality' => 'auto',
                    'fetch_format' => 'jpg'
                ]
            ]);

            $s3_path = $this->base_folder . '/' . $campaign->uuid . '/' . $name . '.jpg';
            
            $image_contents = file_get_contents($cloudinary_file['secure_url']);
            
            $s3_result = Storage::disk('s3')->put($s3_path, $image_contents);
    
            if ($s3_result) {
                
                // Crea una nueva promoción
                $promotion = MarketingPromotion::create([
                    'sort_id' => $this->sort_id,
                    'name' => $this->promotion_name,
                    'spec_sheet' => $this->spec_sheet,
                    'image_path' => $this->aws_url . '/' . $s3_path
                ]);
            
            } else {
                throw new Exception('Failed to upload image to S3');
            }
    
            // Asocia la promoción con la campaña
            $campaign->promotions()->attach($promotion->id);
    
            Log::info('Promotion associated successfully:', [
                'promotion_id' => $promotion->id,
                'campaign_id' => $campaign->id,
                'image_path' => $promotion->image_path
            ]);

            $cloudinary->uploadApi()->destroy($cloudinary_file['public_id']);
    
            Storage::delete($this->path);
    
            ApiResponseHelper::imageSuccess(200, 'Imagen subida correctamente al servicio externo', ['url' => $this->aws_url . '/' . $s3_path]);
    
        } catch (Exception $e) {
            // Manejo de excepciones
            Log::error('Error uploading image:', ['exception' => $e->getMessage()]);
            ApiResponseHelper::imageError('Error en el job para subir la imagen para id: ' . $this->campaign_uuid, $e->getMessage(), 500, 'UPLOAD_IMAGE_ERROR');
        }
    }

    /**
     * Validates the required inputs.
     */
    protected function validateInputs(): void
    {
        $requiredFields = [
            'path' => $this->path,
            'campaign_uuid' => $this->campaign_uuid,
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
