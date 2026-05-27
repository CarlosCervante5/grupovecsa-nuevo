<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Formatos de imagen aceptados (incl. HEIC/HEIF de iPhone).
 * Cloudinary convierte a JPG en los jobs de subida (fetch_format).
 */
final class UploadableImage
{
    /** @var list<string> */
    public const EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif',
    ];

    /** @var list<string> */
    public const MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/heic',
        'image/heif',
        'image/heic-sequence',
        'image/heif-sequence',
    ];

    public static function isAllowed(UploadedFile $file, bool $allowPdf = false): bool
    {
        if (! $file->isValid()) {
            return false;
        }

        $ext = self::normalizeExtension($file);
        if (in_array($ext, self::EXTENSIONS, true)) {
            return true;
        }

        if ($allowPdf && $ext === 'pdf') {
            return true;
        }

        $mime = strtolower((string) $file->getMimeType());
        if (in_array($mime, self::MIME_TYPES, true)) {
            return true;
        }

        return $allowPdf && $mime === 'application/pdf';
    }

    /**
     * Guarda en disco local conservando extensión (Cloudinary detecta HEIC).
     */
    public static function storeTemp(UploadedFile $file, string $directory = 'temp_images'): string
    {
        $ext = self::normalizeExtension($file);
        $name = Str::uuid()->toString().'.'.$ext;

        return $file->storeAs($directory, $name);
    }

    public static function normalizeExtension(UploadedFile $file): string
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (in_array($ext, ['heif', 'heics'], true)) {
            return 'heic';
        }
        if ($ext === 'jpeg') {
            return 'jpg';
        }
        if (in_array($ext, self::EXTENSIONS, true)) {
            return $ext;
        }

        $mime = strtolower((string) $file->getMimeType());
        if (str_contains($mime, 'heic') || str_contains($mime, 'heif')) {
            return 'heic';
        }
        if (str_contains($mime, 'png')) {
            return 'png';
        }
        if (str_contains($mime, 'gif')) {
            return 'gif';
        }
        if (str_contains($mime, 'webp')) {
            return 'webp';
        }

        return 'jpg';
    }

    /**
     * Opciones de subida Cloudinary: salida JPG (HEIC iPhone → JPG).
     *
     * @return array{quality: string, fetch_format: string}
     */
    public static function cloudinaryJpgTransformation(): array
    {
        return [
            'quality' => 'auto',
            'fetch_format' => 'jpg',
        ];
    }
}
