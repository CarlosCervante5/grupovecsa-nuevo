<?php

namespace App\Jobs;

use App\Models\MarketingPost;
use App\Models\MarketingPostGalleryImage;
use App\Support\UploadableImage;
use Cloudinary\Cloudinary;
use Exception;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadExperiencePostGalleryImage
{
    use Dispatchable;

    public function __construct(
        protected string $path,
        protected MarketingPost $post,
        protected string $originalFilename,
        protected int $sortId
    ) {}

    public function handle(Cloudinary $cloudinary): void
    {
        try {
            $name = time().'_'.$this->sortId.'_'.substr(md5($this->originalFilename), 0, 8);

            $cloudinaryFile = $cloudinary->uploadApi()->upload(storage_path('app/'.$this->path), [
                'public_id' => $name,
                'folder' => $this->baseFolder().'/'.$this->post->uuid.'/gallery',
                'transformation' => UploadableImage::cloudinaryJpgTransformation(),
            ]);

            $s3Path = $this->baseFolder().'/'.$this->post->uuid.'/gallery/'.$name.'.jpg';
            $imageContents = file_get_contents($cloudinaryFile['secure_url']);
            $s3Result = Storage::disk('s3')->put($s3Path, $imageContents);

            if (! $s3Result) {
                throw new Exception('Failed to upload gallery image to S3');
            }

            MarketingPostGalleryImage::create([
                'sort_id' => $this->sortId,
                'image_path' => $this->awsUrl().'/'.$s3Path,
                'image_name' => $this->originalFilename,
                'post_id' => $this->post->id,
            ]);

            $cloudinary->uploadApi()->destroy($cloudinaryFile['public_id']);
            Storage::delete($this->path);
        } catch (\Exception $e) {
            Log::error('UploadExperiencePostGalleryImage failed', [
                'post_uuid' => $this->post->uuid,
                'path' => $this->path,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function baseFolder(): string
    {
        return env('AWS_BLOGS_FOLDER_BASE', 'default_folder');
    }

    private function awsUrl(): string
    {
        return rtrim((string) env('AWS_CLOUDFRONT_URL'), '/');
    }
}
