<?php

namespace App\Services\Media;

use App\Models\Boutique\BoutiqueProductImage;
use App\Models\VehicleImage;
use Illuminate\Support\Facades\Storage;

/**
 * Guarda el resultado de IA en S3 y actualiza el registro de imagen.
 */
final class ImageAiPersistenceService
{
    public function persistVehicleImage(VehicleImage $vehicleImage, string $vehicleUuid, string $imageContents, string $extension = 'jpg'): string
    {
        $baseFolder = trim((string) env('AWS_VEHICLES_FOLDER_BASE', 'default_folder'));
        $awsUrl = rtrim((string) env('AWS_CLOUDFRONT_URL'), '/');
        $ext = $extension === 'png' ? 'png' : 'jpg';
        $name = time().'_ai_'.$vehicleImage->sort_id;
        $s3Path = "{$baseFolder}/{$vehicleUuid}/{$name}.{$ext}";

        if (! Storage::disk('s3')->put($s3Path, $imageContents)) {
            throw new \RuntimeException('No se pudo guardar la imagen en almacenamiento.');
        }

        $url = $awsUrl.'/'.$s3Path;
        $vehicleImage->update(['service_image_url' => $url]);

        return $url;
    }

    public function persistBoutiqueProductImage(
        BoutiqueProductImage $productImage,
        string $productUuid,
        string $imageContents,
        string $extension = 'jpg'
    ): string {
        $baseFolder = trim((string) env('CLOUDINARY_BOUTIQUE_FOLDER_BASE', 'vecsa_boutique_products'));
        $awsUrl = rtrim((string) env('AWS_CLOUDFRONT_URL'), '/');
        $ext = $extension === 'png' ? 'png' : 'jpg';
        $name = time().'_ai_'.$productImage->sort_id;
        $s3Path = "{$baseFolder}/{$productUuid}/{$name}.{$ext}";

        if (! Storage::disk('s3')->put($s3Path, $imageContents)) {
            throw new \RuntimeException('No se pudo guardar la imagen en almacenamiento.');
        }

        $url = $awsUrl.'/'.$s3Path;
        $productImage->update([
            'image_path' => $url,
            'status' => 'uploaded',
        ]);

        return $url;
    }
}
