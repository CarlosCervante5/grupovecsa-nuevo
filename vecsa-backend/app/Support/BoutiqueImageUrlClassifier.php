<?php

namespace App\Support;

/**
 * Clasifica URLs de imágenes boutique para auditoría y validación de dumps.
 */
final class BoutiqueImageUrlClassifier
{
    public const CLOUDFRONT = 'cloudfront';

    public const CLOUDINARY = 'cloudinary';

    public const WORDPRESS = 'wordpress';

    public const OTHER = 'other';

    public const EMPTY = 'empty';

    public static function classify(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return self::EMPTY;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = strtolower($url);

        if ($host !== '' && str_contains($host, 'cloudfront.net')) {
            return self::CLOUDFRONT;
        }

        $cloudfrontHost = self::cloudfrontHost();
        if ($cloudfrontHost !== '' && $host === $cloudfrontHost) {
            return self::CLOUDFRONT;
        }

        if ($host !== '' && str_contains($host, 'res.cloudinary.com')) {
            return self::CLOUDINARY;
        }

        if (self::isWordPressUrl($url)) {
            return self::WORDPRESS;
        }

        return self::OTHER;
    }

    public static function isCdnUrl(?string $url): bool
    {
        return in_array(self::classify($url), [self::CLOUDFRONT, self::CLOUDINARY], true);
    }

    public static function isWordPressUrl(?string $url): bool
    {
        $url = strtolower(trim((string) $url));
        if ($url === '') {
            return false;
        }

        foreach (self::wordpressHosts() as $host) {
            if (str_contains($url, $host)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public static function wordpressHosts(): array
    {
        $configured = trim((string) env('BOUTIQUE_WP_IMAGE_HOSTS', 'vecsaboutique.com,www.vecsaboutique.com'));

        return array_values(array_filter(array_map(
            static fn (string $host): string => strtolower(trim($host)),
            explode(',', $configured)
        )));
    }

    private static function cloudfrontHost(): string
    {
        $base = trim((string) config('filesystems.cloudfront_url', env('AWS_CLOUDFRONT_URL', '')));
        if ($base === '') {
            return '';
        }

        return strtolower((string) parse_url($base, PHP_URL_HOST));
    }
}
