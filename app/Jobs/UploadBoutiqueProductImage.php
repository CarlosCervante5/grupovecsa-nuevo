<?php

namespace App\Jobs;

use App\Helpers\ApiResponseHelper;
use App\Models\Boutique\BoutiqueProduct;
use App\Support\UploadableImage;
use App\Models\Boutique\BoutiqueProductImage;
use Cloudinary\Cloudinary;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadBoutiqueProductImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $product_uuid;
    protected $original_filename;
    public $tries = 5;
    public $backoff = 60;
    protected $base_folder;
    protected $aws_url;

    public function __construct(string $path, string $product_uuid, string $original_filename)
    {
        $this->path = $path;
        $this->product_uuid = $product_uuid;
        $this->original_filename = $original_filename;
        $this->base_folder = env('CLOUDINARY_BOUTIQUE_FOLDER_BASE', 'vecsa_boutique_products');
        $this->aws_url = env('AWS_CLOUDFRONT_URL');
    }

    public function handle(Cloudinary $cloudinary): void
    {
        $this->validateInputs();

        try {
            $product = BoutiqueProduct::findByUuid($this->product_uuid);

            if (!$product) {
                Log::error('BoutiqueProduct not found for UUID: ' . $this->product_uuid);
                return;
            }

            // Find the latest pending image for this product
            $productImage = BoutiqueProductImage::where('product_id', $product->id)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$productImage) {
                Log::error('No pending BoutiqueProductImage found for product: ' . $this->product_uuid);
                return;
            }

            Log::info('UploadBoutiqueProductImage job details:', [
                'product_uuid' => $product->uuid,
                'path' => $this->path,
            ]);

            $name = time() . '_' . pathinfo($this->original_filename, PATHINFO_FILENAME);

            // Upload to Cloudinary
            $cloudinary_file = $cloudinary->uploadApi()->upload(storage_path('app/' . $this->path), [
                'public_id' => $name,
                'folder' => $this->base_folder . '/' . $product->uuid,
                'transformation' => UploadableImage::cloudinaryJpgTransformation(),
            ]);

            $s3_path = $this->base_folder . '/' . $product->uuid . '/' . $name . '.jpg';

            // Copy to S3
            $image_contents = file_get_contents($cloudinary_file['secure_url']);
            $s3_result = Storage::disk('s3')->put($s3_path, $image_contents);

            if ($s3_result) {
                $productImage->update([
                    'image_path' => $this->aws_url . '/' . $s3_path,
                    'cloudinary_public_id' => $cloudinary_file['public_id'],
                    'status' => 'uploaded',
                ]);
            } else {
                throw new Exception('Failed to upload image to S3');
            }

            Log::info('Boutique product image uploaded successfully:', [
                'product_uuid' => $product->uuid,
                'image_path' => $this->aws_url . '/' . $s3_path,
            ]);

            // Delete from Cloudinary
            $cloudinary->uploadApi()->destroy($cloudinary_file['public_id']);

            // Delete temp file
            Storage::delete($this->path);

            ApiResponseHelper::imageSuccess(200, 'Imagen de producto subida correctamente', ['url' => $this->aws_url . '/' . $s3_path]);
        } catch (Exception $e) {
            Log::error('Error uploading boutique product image:', ['exception' => $e->getMessage()]);

            // Mark image as failed
            $product = BoutiqueProduct::findByUuid($this->product_uuid);
            if ($product) {
                BoutiqueProductImage::where('product_id', $product->id)
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->first()
                    ?->update(['status' => 'failed']);
            }

            ApiResponseHelper::imageError('Error en el job para subir la imagen del producto: ' . $this->product_uuid, $e->getMessage(), 500, 'UPLOAD_IMAGE_ERROR');
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
