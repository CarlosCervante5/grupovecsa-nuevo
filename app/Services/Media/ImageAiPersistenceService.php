<?php

namespace App\Services\Media;

use App\Models\Boutique\BoutiqueProductImage;
use App\Models\VehicleImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemException;

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

        $this->putOnS3($s3Path, $imageContents);

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

        $this->putOnS3($s3Path, $imageContents);

        $url = $awsUrl.'/'.$s3Path;
        $productImage->update([
            'image_path' => $url,
            'status' => 'uploaded',
        ]);

        return $url;
    }

    private function putOnS3(string $s3Path, string $contents): void
    {
        if (trim((string) env('AWS_BUCKET', '')) === '') {
            throw new \RuntimeException('AWS_BUCKET no está configurado en el servidor.');
        }

        try {
            $ok = Storage::disk('s3')->put($s3Path, $contents);
        } catch (FilesystemException $e) {
            Log::error('S3 put failed (Flysystem)', ['path' => $s3Path, 'message' => $e->getMessage()]);

            throw new \RuntimeException('Error al guardar en S3: '.$e->getMessage(), 0, $e);
        } catch (\Throwable $e) {
            Log::error('S3 put failed', ['path' => $s3Path, 'message' => $e->getMessage()]);

            throw new \RuntimeException('Error al guardar en S3: '.$e->getMessage(), 0, $e);
        }

        if (! $ok) {
            throw new \RuntimeException(
                'No se pudo guardar la imagen en S3. Revisa AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET y AWS_DEFAULT_REGION.'
            );
        }
    }
}
