<?php

namespace App\Helpers;

/**
 * Convierte HTML (p. ej. exportaciones WooCommerce / Google Sheets) a texto legible.
 */
class RichTextHelper
{
    public static function toPlainText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim($value);
        if ($text === '') {
            return null;
        }

        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('/<\/p>/i', "\n", $text);
        $text = preg_replace('/<\/li>/i', "\n", $text);
        $text = preg_replace('/<li[^>]*>/i', '- ', $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Secuencias literales \n del CSV / JSON
        $text = str_replace(["\\r\\n", "\\r", "\\n"], "\n", $text);
        $text = preg_replace("/\r\n|\r/", "\n", $text);

        $lines = explode("\n", $text);
        $lines = array_map(static function (string $line): string {
            $line = preg_replace('/[ \t]+/u', ' ', trim($line));

            return $line ?? '';
        }, $lines);

        $text = implode("\n", $lines);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        $text = trim($text);

        return $text === '' ? null : $text;
    }
}
