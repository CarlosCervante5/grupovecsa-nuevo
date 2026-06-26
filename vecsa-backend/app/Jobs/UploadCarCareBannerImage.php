<?php

namespace App\Jobs;

use App\Models\CarCare\CarCareBanner;
use App\Services\Media\CloudinaryImageStorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadCarCareBannerImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    public $backoff = 60;

    public function __construct(
        protected string $path,
        protected string $bannerUuid,
        protected string $imageType,
        protected string $originalFilename
    ) {}

    public function handle(CloudinaryImageStorageService $storage): void
    {
        $banner = CarCareBanner::findByUuid($this->bannerUuid);
        if (! $banner) {
            Log::error('CarCareBanner not found for UUID: '.$this->bannerUuid);

            return;
        }

        try {
            $result = $storage->storeFromTempRelativePath(
                trim((string) config('filesystems.carcare_banners_folder_base', 'carcare_banners')),
                $banner->uuid,
                $this->path,
                $this->imageType
            );

            $field = $this->imageType === 'desktop' ? 'desktop_image_path' : 'mobile_image_path';
            $banner->update([$field => $result['url']]);

            Log::info('UploadCarCareBannerImage DONE', [
                'banner_uuid' => $this->bannerUuid,
                'image_type' => $this->imageType,
                'path' => $result['url'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error uploading carcare banner image:', [
                'banner_uuid' => $this->bannerUuid,
                'image_type' => $this->imageType,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
