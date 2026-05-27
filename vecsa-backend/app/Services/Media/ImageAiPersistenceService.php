<?php

namespace App\Services\Media;

use App\Models\Boutique\BoutiqueProductImage;
use App\Models\VehicleImage;

/**
 * Persiste el resultado de IA vía Cloudinary (y S3 solo si está configurado en el servidor).
 */
final class ImageAiPersistenceService
{
    public function __construct(private CloudinaryImageStorageService $storage) {}

    public function persistVehicleImage(VehicleImage $vehicleImage, string $vehicleUuid, string $imageContents, string $extension = 'jpg'): string
    {
        $stored = $this->storage->storeVehicleImageBinary(
            $vehicleUuid,
            $imageContents,
            $extension,
            'ai_'.$vehicleImage->sort_id
        );

        $vehicleImage->update([
            'service_image_url' => $stored['url'],
            'service_public_id' => $stored['public_id'],
        ]);

        return $stored['url'];
    }

    public function persistBoutiqueProductImage(
        BoutiqueProductImage $productImage,
        string $productUuid,
        string $imageContents,
        string $extension = 'jpg'
    ): string {
        $stored = $this->storage->storeBoutiqueImageBinary(
            $productUuid,
            $imageContents,
            $extension,
            'ai_'.$productImage->sort_id
        );

        $productImage->update([
            'image_path' => $stored['url'],
            'cloudinary_public_id' => $stored['public_id'],
            'status' => 'uploaded',
        ]);

        return $stored['url'];
    }
}
