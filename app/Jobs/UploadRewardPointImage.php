<?php

namespace App\Jobs;

use App\Helpers\ApiResponseHelper;
use App\Models\PointImage;
use App\Models\RewardPoint;
use Cloudinary\Cloudinary;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadRewardPointImage  implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $name;
    protected $reward_point;
    protected $original_filename;
    protected $base_folder;
    protected $aws_url;

    /**
     * Create a new job instance.
     */
    public function __construct( String $path, String $name , RewardPoint $reward_point, String $original_filename)
    {
        $this->path = $path;
        $this->name = $name;
        $this->reward_point = $reward_point;
        $this->original_filename = $original_filename;
        $this->base_folder = env('AWS_REWARD_POINTS_FOLDER_BASE', 'default_folder');
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
                'reward_uuid' => $this->reward_point->uuid,
                'path' => $this->path,
            ]);

            $name = time().'_'.$this->reward_point->uuid;

            $cloudinary_file = $cloudinary->uploadApi()->upload(storage_path('app/' . $this->path), [
                'public_id' => $name,
                'folder' => $this->base_folder . '/' . $this->reward_point->uuid,
                'transformation' => [
                    'quality' => 'auto',
                    'fetch_format' => 'jpg'
                ]
            ]);

            $s3_path = $this->base_folder . '/' . $this->reward_point->uuid . '/' . $name . '.jpg';
            
            $image_contents = file_get_contents($cloudinary_file['secure_url']);
            
            $s3_result = Storage::disk('s3')->put($s3_path, $image_contents);

            if ($s3_result) {

                $point_image = PointImage::where([ 'name' => $this->name, 'point_id' => $this->reward_point->id])->first();

                if(!$point_image){
                    $point_image = PointImage::create([
                        'name' => $this->name,
                        'point_id' => $this->reward_point->id,
                        'image_path' => $this->aws_url . '/' . $s3_path,
                    ]);
                } else {
                    $point_image->update([
                        'image_path' => $this->aws_url . '/' . $s3_path,
                    ]);
                }
                
            } else {
                throw new Exception('Failed to upload image to S3');
            }

            $cloudinary->uploadApi()->destroy($cloudinary_file['public_id']);

            Storage::delete($this->path);

            ApiResponseHelper::imageSuccess(200, 'Imagen subida correctamente al servicio externo', ['url' => $this->aws_url . '/' . $s3_path]);

        } catch (\Exception $e) {
        
            ApiResponseHelper::imageError('Error en el job para subir la imagen para uuid: '.$this->reward_point->uuid, $e->getMessage(), 500, 'UPLOAD_IMAGE_ERROR');

            ApiResponseHelper::imageError('Imagen guardada localmente para reward point uuid: '.$this->reward_point->uuid, 'Guardada en: ' . $this->path, 500, 'SAVE_LOCAL_IMAGE_ERROR');
        }
    }

    /**
     * Validates the required inputs.
     */
    protected function validateInputs(): void
    {
        $requiredFields = [
            'path' => $this->path,
            'name' => $this->name,
            'reward_point' => $this->reward_point,
            'original_filename' => $this->original_filename
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                throw new Exception("{$field} is required");
            }
        }
    }
}
