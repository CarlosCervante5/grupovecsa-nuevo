<?php

namespace App\Services\Boutique;

/**
 * Normaliza enlaces de Google Drive a URL de descarga directa.
 */
class BoutiqueExternalImageUrlResolver
{
    public function isGoogleDriveUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if (str_contains($host, 'drive.google.com')) {
            return true;
        }

        if (str_contains($host, 'drive.usercontent.google.com')) {
            return true;
        }

        if ($host === 'docs.google.com' && str_contains($url, '/uc')) {
            return true;
        }

        return false;
    }

    /**
     * ID de archivo en Drive, o null si no se puede extraer.
     */
    public function extractDriveFileId(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (preg_match('#drive\.google\.com/file/d/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return $m[1];
        }

        if (preg_match('#drive\.google\.com/file/u/\d+/d/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return $m[1];
        }

        if (preg_match('#drive\.google\.com/d/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return $m[1];
        }

        if (preg_match('#[?&]id=([a-zA-Z0-9_-]+)#', $url, $m)) {
            return $m[1];
        }

        if (preg_match('#drive\.usercontent\.google\.com/download\?[^#]*\bid=([a-zA-Z0-9_-]+)#', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * URL para descargar el binario (uc export). Requiere archivo compartido como "Cualquier persona con el enlace".
     */
    public function directDownloadUrl(string $fileId): string
    {
        return 'https://drive.google.com/uc?export=download&id='.urlencode($fileId);
    }
}
