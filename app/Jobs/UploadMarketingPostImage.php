<?php

namespace App\Jobs;

use App\Models\MarketingPost;
use App\Support\UploadableImage;
use Cloudinary\Cloudinary;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadMarketingPostImage
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $post;
    protected $original_filename;
    protected $base_folder;
    protected $aws_url;

    /**
     * Create a new job instance.
     */
    public function __construct( String $path, MarketingPost $post, String $original_filename)
    {
        $this->path = $path;
        $this->post = $post;
        $this->original_filename = $original_filename;
        $this->base_folder = env('AWS_BLOGS_FOLDER_BASE', 'default_folder');
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
                'campaign_uuid' => $this->post->uuid,
                'path' => $this->path,
            ]);

            $name = time().'_'.$this->post->uuid;

            $cloudinary_file = $cloudinary->uploadApi()->upload(storage_path('app/' . $this->path), [
                'public_id' => $name,
                'folder' => $this->base_folder . '/' . $this->post->uuid,
                'transformation' => UploadableImage::cloudinaryJpgTransformation(),
            ]);

            $s3_path = $this->base_folder . '/' . $this->post->uuid . '/' . $name . '.jpg';
            
            $image_contents = file_get_contents($cloudinary_file['secure_url']);
            
            $s3_result = Storage::disk('s3')->put($s3_path, $image_contents);

            if ($s3_result) {

                $this->post->update(['image_path' => $this->aws_url . '/' . $s3_path]);

            } else {
                throw new Exception('Failed to upload image to S3');
            }

            $cloudinary->uploadApi()->destroy($cloudinary_file['public_id']);

            Storage::delete($this->path);

        } catch (\Exception $e) {
            Log::error('UploadMarketingPostImage failed', [
                'post_uuid' => $this->post->uuid,
                'path' => $this->path,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Validates the required inputs.
     */
    protected function validateInputs(): void
    {
        $requiredFields = [
            'path' => $this->path,
            'post' => $this->post,
            'original_filename' => $this->original_filename
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                throw new Exception("{$field} is required");
            }
        }
    }
}
