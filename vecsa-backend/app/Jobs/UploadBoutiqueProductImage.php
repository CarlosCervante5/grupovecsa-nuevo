<?php

namespace App\Jobs;

use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductImage;
use App\Services\Media\CloudinaryImageStorageService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadBoutiqueProductImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $product_uuid;
    protected $original_filename;
    public $tries = 5;
    public $backoff = 60;

    public function __construct(string $path, string $product_uuid, string $original_filename)
    {
        $this->path = $path;
        $this->product_uuid = $product_uuid;
        $this->original_filename = $original_filename;
    }

    public function handle(CloudinaryImageStorageService $storage): void
    {
        $this->validateInputs();

        $product = BoutiqueProduct::findByUuid($this->product_uuid);
        if (! $product) {
            Log::error('BoutiqueProduct not found for UUID: '.$this->product_uuid);

            return;
        }

        $productImage = BoutiqueProductImage::where('product_id', $product->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $productImage) {
            Log::error('No pending BoutiqueProductImage found for product: '.$this->product_uuid);

            return;
        }

        Log::info('UploadBoutiqueProductImage job details:', [
            'product_uuid' => $product->uuid,
            'path' => $this->path,
        ]);

        try {
            $nameSuffix = time().'_'.pathinfo($this->original_filename, PATHINFO_FILENAME);
            $result = $storage->storeFromTempRelativePath(
                trim((string) config('filesystems.boutique_folder_base', 'vecsa_boutique_products')),
                $product->uuid,
                $this->path,
                $nameSuffix
            );

            $productImage->update([
                'image_path' => $result['url'],
                'cloudinary_public_id' => $result['public_id'],
                'status' => 'uploaded',
            ]);

            Log::info('Boutique product image uploaded successfully:', [
                'product_uuid' => $product->uuid,
                'image_path' => $result['url'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error uploading boutique product image:', ['exception' => $e->getMessage()]);

            BoutiqueProductImage::where('product_id', $product->id)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->first()
                ?->update(['status' => 'failed']);

            throw $e;
        }
    }

    protected function validateInputs(): void
    {
        $requiredFields = [
            'path' => $this->path,
            'product_uuid' => $this->product_uuid,
            'original_filename' => $this->original_filename,
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty($value)) {
                throw new Exception("{$field} is required");
            }
        }
    }
}
