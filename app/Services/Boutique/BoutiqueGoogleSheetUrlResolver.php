<?php

namespace App\Services\Boutique;

class BoutiqueGoogleSheetUrlResolver
{
    /**
     * Convierte URL de edición o compartida de Google Sheets a export CSV.
     */
    public function resolveExportCsvUrl(?string $input, ?string $gid = null): string
    {
        $input = trim((string) $input);
        if ($input === '') {
            $input = trim((string) config('boutique.google_sheet.default_url', ''));
        }
        if ($input === '') {
            throw new \InvalidArgumentException(
                'Indica la URL de la hoja (sheet_url) o configura BOUTIQUE_GOOGLE_SHEET_URL en el servidor.'
            );
        }

        if (preg_match('#/export\?format=csv#i', $input)) {
            return $this->appendGid($input, $gid);
        }

        if (! preg_match('#/spreadsheets/d/([a-zA-Z0-9_-]+)#', $input, $match)) {
            throw new \InvalidArgumentException('URL de Google Sheets no reconocida.');
        }

        $spreadsheetId = $match[1];
        $resolvedGid = $gid ?? $this->extractGidFromUrl($input) ?? '0';

        return sprintf(
            'https://docs.google.com/spreadsheets/d/%s/export?format=csv&gid=%s',
            $spreadsheetId,
            rawurlencode($resolvedGid)
        );
    }

    private function appendGid(string $url, ?string $gid): string
    {
        if ($gid === null || $gid === '') {
            return $url;
        }
        $parsed = parse_url($url);
        parse_str($parsed['query'] ?? '', $query);
        $query['gid'] = $gid;

        return ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? '')
            .($parsed['path'] ?? '').'?'.http_build_query($query);
    }

    private function extractGidFromUrl(string $url): ?string
    {
        if (preg_match('/[?&#]gid=(\d+)/', $url, $m)) {
            return $m[1];
        }

        return null;
    }
}
