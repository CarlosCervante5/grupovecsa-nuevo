<?php

namespace App\Services\Media;

use App\Support\UploadableImage;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemException;

/**
 * Sube imágenes vía Cloudinary. Si hay credenciales S3, replica el flujo del inventario
 * (Cloudinary → S3 → CloudFront). Si no, deja el asset en Cloudinary y usa secure_url.
 */
final class CloudinaryImageStorageService
{
    public function __construct(private Cloudinary $cloudinary) {}

    /**
     * @return array{url: string, public_id: string|null}
     */
    public function storeVehicleImageBinary(
        string $vehicleUuid,
        string $contents,
        string $extension = 'jpg',
        ?string $nameSuffix = null
    ): array {
        return $this->uploadBinary(
            $this->vehiclesFolderBase(),
            $vehicleUuid,
            $contents,
            $extension,
            $nameSuffix
        );
    }

    /**
     * @return array{url: string, public_id: string|null}
     */
    public function storeBoutiqueImageBinary(
        string $productUuid,
        string $contents,
        string $extension = 'jpg',
        ?string $nameSuffix = null
    ): array {
        return $this->uploadBinary(
            $this->boutiqueFolderBase(),
            $productUuid,
            $contents,
            $extension,
            $nameSuffix
        );
    }

    /**
     * Sube un archivo ya guardado en storage/app (p. ej. temp de UploadableImage).
     *
     * @return array{url: string, public_id: string|null}
     */
    public function storeFromTempRelativePath(
        string $baseFolder,
        string $entityUuid,
        string $tempRelativePath,
        ?string $nameSuffix = null,
        string $extension = 'jpg'
    ): array {
        $fullPath = storage_path('app/'.$tempRelativePath);
        if (! is_file($fullPath)) {
            throw new \RuntimeException('Archivo temporal de imagen no encontrado.');
        }

        $contents = file_get_contents($fullPath);
        if ($contents === false || $contents === '') {
            throw new \RuntimeException('No se pudo leer la imagen temporal.');
        }

        try {
            return $this->uploadBinary($baseFolder, $entityUuid, $contents, $extension, $nameSuffix);
        } finally {
            Storage::delete($tempRelativePath);
        }
    }

    /**
     * @return array{url: string, public_id: string|null}
     */
    private function uploadBinary(
        string $baseFolder,
        string $entityUuid,
        string $contents,
        string $extension,
        ?string $nameSuffix
    ): array {
        $this->assertCloudinaryConfigured();

        $format = $extension === 'png' ? 'png' : 'jpg';
        $tempRelative = 'temp_images/ai_'.uniqid('', true).'.'.$format;
        $cloudinaryFormat = $format === 'png' ? 'png' : 'jpg';

        if (! Storage::put($tempRelative, $contents)) {
            throw new \RuntimeException('No se pudo preparar la imagen temporal para subir.');
        }

        try {
            $name = time().($nameSuffix !== null && $nameSuffix !== '' ? '_'.$nameSuffix : '');
            $folder = $baseFolder.'/'.$entityUuid;

            $cloudinaryFile = $this->normalizeCloudinaryUploadResponse(
                $this->cloudinary->uploadApi()->upload(storage_path('app/'.$tempRelative), [
                    'public_id' => $name,
                    'folder' => $folder,
                    'transformation' => $cloudinaryFormat === 'jpg'
                        ? UploadableImage::cloudinaryJpgTransformation()
                        : ['quality' => 'auto', 'fetch_format' => 'png'],
                ])
            );

            $publicId = (string) ($cloudinaryFile['public_id'] ?? '');
            $secureUrl = (string) ($cloudinaryFile['secure_url'] ?? '');

            if ($secureUrl === '') {
                throw new \RuntimeException('No se pudo publicar la imagen procesada.');
            }

            if ($this->s3IsConfigured()) {
                return $this->mirrorCloudinaryToS3($cloudinaryFile, $folder, $name, $format, $publicId);
            }

            Log::info('Image stored on Cloudinary only (S3 not configured)', [
                'folder' => $folder,
                'public_id' => $publicId,
            ]);

            return [
                'url' => $secureUrl,
                'public_id' => $publicId !== '' ? $publicId : null,
            ];
        } finally {
            Storage::delete($tempRelative);
        }
    }

    /**
     * @param  array<string, mixed>  $cloudinaryFile
     * @return array{url: string, public_id: string|null}
     */
    private function mirrorCloudinaryToS3(
        array $cloudinaryFile,
        string $folder,
        string $name,
        string $format,
        string $publicId
    ): array {
        $s3Path = $folder.'/'.$name.'.'.$format;
        $imageContents = file_get_contents((string) $cloudinaryFile['secure_url']);
        if ($imageContents === false || $imageContents === '') {
            throw new \RuntimeException('No se pudo obtener la imagen procesada.');
        }

        try {
            $ok = Storage::disk('s3')->put($s3Path, $imageContents);
        } catch (FilesystemException $e) {
            Log::error('S3 put after Cloudinary failed', ['path' => $s3Path, 'message' => $e->getMessage()]);

            throw new \RuntimeException('Error al guardar la imagen: '.$e->getMessage(), 0, $e);
        }

        if (! $ok) {
            throw new \RuntimeException('No se pudo guardar la imagen en el servidor.');
        }

        if ($publicId !== '') {
            $this->cloudinary->uploadApi()->destroy($publicId);
        }

        $awsUrl = $this->cloudfrontBaseUrl();
        $url = $awsUrl !== '' ? $awsUrl.'/'.$s3Path : $s3Path;

        return ['url' => $url, 'public_id' => null];
    }

    private function s3IsConfigured(): bool
    {
        return trim((string) config('filesystems.disks.s3.bucket', '')) !== ''
            && trim((string) config('filesystems.disks.s3.key', '')) !== ''
            && trim((string) config('filesystems.disks.s3.secret', '')) !== ''
            && trim((string) config('filesystems.disks.s3.region', '')) !== '';
    }

    private function assertCloudinaryConfigured(): void
    {
        if (trim((string) config('cloudinary.url', '')) === '') {
            throw new \RuntimeException(
                'El almacenamiento de imágenes no está configurado en el servidor.'
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

    /**
     * Cloudinary SDK ≥3 devuelve ApiResponse (ArrayObject), no array nativo.
     *
     * @return array<string, mixed>
     */
    private function normalizeCloudinaryUploadResponse(mixed $response): array
    {
        if (is_array($response)) {
            return $response;
        }

        if ($response instanceof \ArrayObject) {
            return $response->getArrayCopy();
        }

        throw new \RuntimeException('Respuesta inesperada al subir imagen a Cloudinary.');
    }
}
