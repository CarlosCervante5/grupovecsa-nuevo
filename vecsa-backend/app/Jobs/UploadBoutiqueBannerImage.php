<?php

namespace App\Jobs;

use App\Helpers\ApiResponseHelper;
use App\Models\Boutique\BoutiqueBanner;
use Cloudinary\Cloudinary;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadBoutiqueBannerImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $banner_uuid;
    protected $image_type;
    protected $original_filename;
    public $tries = 5;
    public $backoff = 60;
    protected $base_folder;
    protected $aws_url;

    public function __construct(string $path, string $banner_uuid, string $image_type, string $original_filename)
    {
        $this->path = $path;
        $this->banner_uuid = $banner_uuid;
        $this->image_type = $image_type;
        $this->original_filename = $original_filename;
        $this->base_folder = env('CLOUDINARY_BOUTIQUE_BANNERS_FOLDER_BASE', 'boutique_banners');
        $this->aws_url = env('AWS_CLOUDFRONT_URL');
    }

    public function handle(Cloudinary $cloudinary): void
    {
        try {
            $banner = BoutiqueBanner::findByUuid($this->banner_uuid);
            if (!$banner) {
                Log::error('BoutiqueBanner not found for UUID: ' . $this->banner_uuid);
                return;
            }

            $name = time() . '_' . $this->image_type;

            $cloudinary_file = $cloudinary->uploadApi()->upload(storage_path('app/' . $this->path), [
                'public_id' => $name,
                'folder' => $this->base_folder . '/' . $banner->uuid,
                'transformation' => ['quality' => 'auto', 'fetch_format' => 'jpg']
            ]);

            $s3_path = $this->base_folder . '/' . $banner->uuid . '/' . $name . '.jpg';
            $image_contents = file_get_contents($cloudinary_file['secure_url']);
            $s3_result = Storage::disk('s3')->put($s3_path, $image_contents);

            if ($s3_result) {
                $field = $this->image_type === 'desktop' ? 'desktop_image_path' : 'mobile_image_path';
                $banner->update([$field => $this->aws_url . '/' . $s3_path]);

                Log::info('UploadBoutiqueBannerImage DONE', [
                    'banner_uuid' => $this->banner_uuid,
                    'image_type' => $this->image_type,
                    'path' => $this->aws_url . '/' . $s3_path,
                ]);
            } else {
                throw new Exception('Failed to upload image to S3');
            }

            $cloudinary->uploadApi()->destroy($cloudinary_file['public_id']);
            Storage::delete($this->path);

        } catch (Exception $e) {
            Log::error('Error uploading boutique banner image:', [
                'banner_uuid' => $this->banner_uuid,
                'image_type' => $this->image_type,
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
