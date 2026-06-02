<?php

namespace App\Services\Boutique;

use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductImage;
use App\Services\Media\CloudinaryImageStorageService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Descarga imágenes desde Google Drive y las publica en Cloudinary/S3 (mismo flujo que subida manual).
 */
class BoutiqueExternalImageImporter
{
    private const MAX_BYTES = 15 * 1024 * 1024;

    public function __construct(
        private readonly BoutiqueExternalImageUrlResolver $urlResolver,
        private readonly CloudinaryImageStorageService $storage,
    ) {}

    public function isGoogleDriveUrl(string $url): bool
    {
        return $this->urlResolver->isGoogleDriveUrl($url);
    }

    /**
     * Importa una imagen de Drive al CDN y crea el registro. Devuelve false si falla.
     */
    public function importDriveImage(BoutiqueProduct $product, string $rawUrl, int $sortId): bool
    {
        $rawUrl = trim($rawUrl);
        if (! $this->urlResolver->isGoogleDriveUrl($rawUrl)) {
            return false;
        }

        $fileId = $this->urlResolver->extractDriveFileId($rawUrl);
        if ($fileId === null) {
            Log::warning('Google Sheet image: Drive URL sin ID reconocible', [
                'product_uuid' => $product->uuid,
                'url' => $rawUrl,
            ]);

            return false;
        }

        try {
            $binary = $this->downloadDriveFile($fileId);
            if ($binary === null) {
                return false;
            }

            $extension = $this->detectImageExtension($binary['body'], $binary['content_type']);
            $stored = $this->storage->storeBoutiqueImageBinary(
                $product->uuid,
                $binary['body'],
                $extension,
                'gsheet_'.$sortId
            );

            BoutiqueProductImage::create([
                'product_id' => $product->id,
                'image_path' => $stored['url'],
                'cloudinary_public_id' => $stored['public_id'],
                'sort_id' => $sortId,
                'status' => 'uploaded',
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Google Sheet image: fallo al importar desde Drive', [
                'product_uuid' => $product->uuid,
                'file_id' => $fileId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @return array{body: string, content_type: string|null}|null
     */
    private function downloadDriveFile(string $fileId): ?array
    {
        $baseUrl = $this->urlResolver->directDownloadUrl($fileId);

        $response = $this->httpClient()->get($baseUrl);
        if (! $response->successful()) {
            Log::warning('Google Sheet image: HTTP error al descargar Drive', [
                'file_id' => $fileId,
                'status' => $response->status(),
            ]);

            return null;
        }

        $contentType = strtolower((string) $response->header('Content-Type'));
        $body = (string) $response->body();

        if ($this->looksLikeHtml($contentType, $body)) {
            $confirmUrl = $this->buildConfirmDownloadUrl($baseUrl, $body);
            if ($confirmUrl !== null) {
                $response = $this->httpClient()->get($confirmUrl);
                if ($response->successful()) {
                    $contentType = strtolower((string) $response->header('Content-Type'));
                    $body = (string) $response->body();
                }
            }
        }

        if ($this->looksLikeHtml($contentType, $body)) {
            Log::warning('Google Sheet image: Drive devolvió HTML (permisos o enlace inválido)', [
                'file_id' => $fileId,
            ]);

            return null;
        }

        if (strlen($body) > self::MAX_BYTES) {
            Log::warning('Google Sheet image: archivo demasiado grande', ['file_id' => $fileId]);

            return null;
        }

        if (! $this->isImageBinary($body)) {
            Log::warning('Google Sheet image: contenido no es imagen', ['file_id' => $fileId]);

            return null;
        }

        return [
            'body' => $body,
            'content_type' => $contentType !== '' ? $contentType : null,
        ];
    }

    private function httpClient(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout(90)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; GrupoVECSA-Boutique/1.0)',
            ]);
    }

    private function looksLikeHtml(string $contentType, string $body): bool
    {
        if (str_contains($contentType, 'text/html')) {
            return true;
        }

        $start = ltrim(substr($body, 0, 200));

        return str_starts_with(strtolower($start), '<!doctype')
            || str_starts_with(strtolower($start), '<html');
    }

    private function buildConfirmDownloadUrl(string $baseUrl, string $html): ?string
    {
        if (preg_match('/confirm=([0-9A-Za-z_-]+)/', $html, $m)) {
            $sep = str_contains($baseUrl, '?') ? '&' : '?';

            return $baseUrl.$sep.'confirm='.$m[1];
        }

        if (preg_match('/name="uuid"\s+value="([a-f0-9-]+)"/i', $html, $m)) {
            $sep = str_contains($baseUrl, '?') ? '&' : '?';

            return $baseUrl.$sep.'confirm=t&uuid='.$m[1];
        }

        return null;
    }

    private function isImageBinary(string $body): bool
    {
        if ($body === '') {
            return false;
        }

        $info = @getimagesizefromstring($body);

        return $info !== false;
    }

    private function detectImageExtension(string $body, ?string $contentType): string
    {
        $info = @getimagesizefromstring($body);
        if ($info !== false && isset($info[2])) {
            return match ($info[2]) {
                IMAGETYPE_PNG => 'png',
                IMAGETYPE_GIF => 'gif',
                IMAGETYPE_WEBP => 'webp',
                default => 'jpg',
            };
        }

        if ($contentType !== null) {
            if (str_contains($contentType, 'png')) {
                return 'png';
            }
            if (str_contains($contentType, 'gif')) {
                return 'gif';
            }
            if (str_contains($contentType, 'webp')) {
                return 'webp';
            }
        }

        return 'jpg';
    }
}
