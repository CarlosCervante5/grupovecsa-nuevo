<?php

namespace App\Services\Media;

use App\Models\Boutique\BoutiqueProductImage;
use App\Models\VehicleImage;

/**
 * Persiste el resultado de IA con el mismo pipeline Cloudinary → S3 que las subidas normales.
 */
final class ImageAiPersistenceService
{
    public function __construct(private CloudinaryToS3ImageService $storage) {}

    public function persistVehicleImage(VehicleImage $vehicleImage, string $vehicleUuid, string $imageContents, string $extension = 'jpg'): string
    {
        $url = $this->storage->storeVehicleImageBinary(
            $vehicleUuid,
            $imageContents,
            $extension,
            'ai_'.$vehicleImage->sort_id
        );

        $vehicleImage->update(['service_image_url' => $url]);

        return $url;
    }

    public function persistBoutiqueProductImage(
        BoutiqueProductImage $productImage,
        string $productUuid,
        string $imageContents,
        string $extension = 'jpg'
    ): string {
        $url = $this->storage->storeBoutiqueImageBinary(
            $productUuid,
            $imageContents,
            $extension,
            'ai_'.$productImage->sort_id
        );

        $productImage->update([
            'image_path' => $url,
            'status' => 'uploaded',
        ]);

        return $url;
    }
}
