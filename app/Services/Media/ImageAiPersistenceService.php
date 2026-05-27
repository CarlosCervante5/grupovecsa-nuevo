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
        $baseFolder = $this->vehiclesFolderBase();
        $awsUrl = $this->cloudfrontBaseUrl();
        $ext = $extension === 'png' ? 'png' : 'jpg';
        $name = time().'_ai_'.$vehicleImage->sort_id;
        $s3Path = "{$baseFolder}/{$vehicleUuid}/{$name}.{$ext}";

        $this->putOnS3($s3Path, $imageContents);

        $url = $awsUrl !== '' ? $awsUrl.'/'.$s3Path : $s3Path;
        $vehicleImage->update(['service_image_url' => $url]);

        return $url;
    }

    public function persistBoutiqueProductImage(
        BoutiqueProductImage $productImage,
        string $productUuid,
        string $imageContents,
        string $extension = 'jpg'
    ): string {
        $baseFolder = $this->boutiqueFolderBase();
        $awsUrl = $this->cloudfrontBaseUrl();
        $ext = $extension === 'png' ? 'png' : 'jpg';
        $name = time().'_ai_'.$productImage->sort_id;
        $s3Path = "{$baseFolder}/{$productUuid}/{$name}.{$ext}";

        $this->putOnS3($s3Path, $imageContents);

        $url = $awsUrl !== '' ? $awsUrl.'/'.$s3Path : $s3Path;
        $productImage->update([
            'image_path' => $url,
            'status' => 'uploaded',
        ]);

        return $url;
    }

    private function putOnS3(string $s3Path, string $contents): void
    {
        $bucket = (string) config('filesystems.disks.s3.bucket', '');
        if ($bucket === '') {
            throw new \RuntimeException(
                'El disco S3 no tiene bucket configurado. En Railway define AWS_BUCKET (y credenciales AWS) en el servicio backend.'
            );
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
                'No se pudo guardar la imagen en S3. Revisa AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET y AWS_DEFAULT_REGION en el servidor.'
            );
        }
    }

    private function cloudfrontBaseUrl(): string
    {
        return rtrim((string) config('filesystems.cloudfront_url', ''), '/');
    }

    private function vehiclesFolderBase(): string
    {
        return trim((string) config('filesystems.vehicles_folder_base', 'default_folder'));
    }

    private function boutiqueFolderBase(): string
    {
        return trim((string) config('filesystems.boutique_folder_base', 'vecsa_boutique_products'));
    }
}
