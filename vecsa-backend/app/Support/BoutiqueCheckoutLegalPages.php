<?php

namespace App\Support;

use App\Models\SystemSetting;

/**
 * Rutas o URLs de avisos legales mostrados en el checkout de la boutique.
 */
final class BoutiqueCheckoutLegalPages
{
    public const SETTING_TERMS = 'boutique_checkout_legal_terms_url';

    public const SETTING_PRIVACY = 'boutique_checkout_legal_privacy_url';

    public const SETTING_RETURNS = 'boutique_checkout_legal_returns_url';

    private const DEFAULT_TERMS = '/condiciones-uso';

    private const DEFAULT_PRIVACY = '/aviso-privacidad';

    private const DEFAULT_RETURNS = '/politicas-devolucion';

    /**
     * @return array{terms_url: string, privacy_url: string, returns_url: string}
     */
    public static function publicPayload(): array
    {
        return [
            'terms_url' => self::resolve(self::SETTING_TERMS, self::DEFAULT_TERMS),
            'privacy_url' => self::resolve(self::SETTING_PRIVACY, self::DEFAULT_PRIVACY),
            'returns_url' => self::resolve(self::SETTING_RETURNS, self::DEFAULT_RETURNS),
        ];
    }

    public static function resolve(string $key, string $default): string
    {
        $raw = trim((string) SystemSetting::get($key, ''));
        if ($raw === '') {
            return $default;
        }
        if (! self::isAllowedUrl($raw)) {
            return $default;
        }

        return self::normalize($raw);
    }

    public static function isAllowedUrl(string $value): bool
    {
        if (strlen($value) > 500) {
            return false;
        }
        if (preg_match('#^https?://#i', $value)) {
            return filter_var($value, FILTER_VALIDATE_URL) !== false;
        }

        return (bool) preg_match('#^/[\w\-./?#=&%:+]*$#u', $value);
    }

    private static function normalize(string $raw): string
    {
        if (preg_match('#^https?://#i', $raw)) {
            return $raw;
        }

        return str_starts_with($raw, '/') ? $raw : '/'.$raw;
    }
}
