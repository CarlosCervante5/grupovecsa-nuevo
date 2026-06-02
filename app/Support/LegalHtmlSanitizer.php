<?php

namespace App\Support;

final class LegalHtmlSanitizer
{
    /**
     * Elimina scripts/iframes/objetos embebidos; el contenido lo edita solo administrador.
     */
    public static function clean(string $html): string
    {
        $html = preg_replace('#<(script|iframe|object|embed|form)[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('#<(script|iframe|object|embed|form)[^>]*/>#i', '', $html) ?? $html;
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;

        return trim($html);
    }
}
