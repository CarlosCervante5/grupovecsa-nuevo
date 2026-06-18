<?php

namespace App\Jobs;

use App\Models\Boutique\BoutiqueBanner;
use App\Services\Media\CloudinaryImageStorageService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadBoutiqueBannerImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $banner_uuid;
    protected $image_type;
    protected $original_filename;
    public $tries = 5;
    public $backoff = 60;

    public function __construct(string $path, string $banner_uuid, string $image_type, string $original_filename)
    {
        $this->path = $path;
        $this->banner_uuid = $banner_uuid;
        $this->image_type = $image_type;
        $this->original_filename = $original_filename;
    }

    public function handle(CloudinaryImageStorageService $storage): void
    {
        $banner = BoutiqueBanner::findByUuid($this->banner_uuid);
        if (! $banner) {
            Log::error('BoutiqueBanner not found for UUID: '.$this->banner_uuid);

            return;
        }

        try {
            $result = $storage->storeFromTempRelativePath(
                trim((string) config('filesystems.boutique_banners_folder_base', 'boutique_banners')),
                $banner->uuid,
                $this->path,
                $this->image_type
            );

            $field = $this->image_type === 'desktop' ? 'desktop_image_path' : 'mobile_image_path';
            $banner->update([$field => $result['url']]);

            Log::info('UploadBoutiqueBannerImage DONE', [
                'banner_uuid' => $this->banner_uuid,
                'image_type' => $this->image_type,
                'path' => $result['url'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error uploading boutique banner image:', [
                'banner_uuid' => $this->banner_uuid,
                'image_type' => $this->image_type,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
