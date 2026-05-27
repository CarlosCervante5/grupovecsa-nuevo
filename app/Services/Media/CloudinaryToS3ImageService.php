<?php

namespace App\Services\Media;

use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemException;

/**
 * Mismo flujo que UploadVehicleImage / UploadBoutiqueProductImage:
 * binario → Cloudinary (transformación) → S3 → URL CloudFront.
 */
final class CloudinaryToS3ImageService
{
    public function __construct(private Cloudinary $cloudinary) {}

    public function storeVehicleImageBinary(
        string $vehicleUuid,
        string $contents,
        string $extension = 'jpg',
        ?string $nameSuffix = null
    ): string {
        return $this->uploadBinary(
            $this->vehiclesFolderBase(),
            $vehicleUuid,
            $contents,
            $extension,
            $nameSuffix
        );
    }

    public function storeBoutiqueImageBinary(
        string $productUuid,
        string $contents,
        string $extension = 'jpg',
        ?string $nameSuffix = null
    ): string {
        return $this->uploadBinary(
            $this->boutiqueFolderBase(),
            $productUuid,
            $contents,
            $extension,
            $nameSuffix
        );
    }

    private function uploadBinary(
        string $baseFolder,
        string $entityUuid,
        string $contents,
        string $extension,
        ?string $nameSuffix
    ): string {
        $this->assertCloudinaryConfigured();
        $this->assertS3Configured();

        $format = $extension === 'png' ? 'png' : 'jpg';
        $tempRelative = 'temp_images/ai_'.uniqid('', true).'.'.$format;

        if (! Storage::put($tempRelative, $contents)) {
            throw new \RuntimeException('No se pudo preparar la imagen temporal para subir.');
        }

        try {
            $name = time().($nameSuffix !== null && $nameSuffix !== '' ? '_'.$nameSuffix : '');
            $folder = $baseFolder.'/'.$entityUuid;

            $cloudinaryFile = $this->cloudinary->uploadApi()->upload(storage_path('app/'.$tempRelative), [
                'public_id' => $name,
                'folder' => $folder,
                'transformation' => [
                    'quality' => 'auto',
                    'fetch_format' => $format,
                ],
            ]);

            $s3Path = $folder.'/'.$name.'.'.$format;
            $imageContents = file_get_contents($cloudinaryFile['secure_url']);
            if ($imageContents === false || $imageContents === '') {
                throw new \RuntimeException('No se pudo obtener la imagen desde Cloudinary.');
            }

            try {
                $ok = Storage::disk('s3')->put($s3Path, $imageContents);
            } catch (FilesystemException $e) {
                Log::error('S3 put after Cloudinary failed', ['path' => $s3Path, 'message' => $e->getMessage()]);

                throw new \RuntimeException('Error al guardar en S3 tras Cloudinary: '.$e->getMessage(), 0, $e);
            }

            if (! $ok) {
                throw new \RuntimeException(
                    'No se pudo guardar en S3 tras Cloudinary. Revisa credenciales AWS y permisos del bucket.'
                );
            }

            $this->cloudinary->uploadApi()->destroy($cloudinaryFile['public_id']);

            $awsUrl = $this->cloudfrontBaseUrl();

            return $awsUrl !== '' ? $awsUrl.'/'.$s3Path : $s3Path;
        } finally {
            Storage::delete($tempRelative);
        }
    }

    private function assertCloudinaryConfigured(): void
    {
        $url = trim((string) config('cloudinary.url', ''));

        if ($url === '') {
            throw new \RuntimeException(
                'CLOUDINARY_URL no está configurado en el servidor. Es necesario para subir imágenes como en el inventario.'
            );
        }
    }

    private function assertS3Configured(): void
    {
        $missing = [];
        if (trim((string) config('filesystems.disks.s3.bucket', '')) === '') {
            $missing[] = 'AWS_BUCKET';
        }
        if (trim((string) config('filesystems.disks.s3.key', '')) === '') {
            $missing[] = 'AWS_ACCESS_KEY_ID';
        }
        if (trim((string) config('filesystems.disks.s3.secret', '')) === '') {
            $missing[] = 'AWS_SECRET_ACCESS_KEY';
        }
        if (trim((string) config('filesystems.disks.s3.region', '')) === '') {
            $missing[] = 'AWS_DEFAULT_REGION';
        }

        if ($missing !== []) {
            throw new \RuntimeException(
                'Faltan variables S3 (destino final tras Cloudinary): '.implode(', ', $missing).'.'
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
