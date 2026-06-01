<?php

namespace App\Support;

class CsvTableReader
{
    /**
     * @return array{headers: list<string>, rows: list<array<string, string>>}
     */
    public static function fromString(string $csv): array
    {
        $handle = fopen('php://memory', 'r+');
        if ($handle === false) {
            throw new \RuntimeException('No se pudo leer el CSV.');
        }
        fwrite($handle, $csv);
        rewind($handle);

        try {
            return self::fromStream($handle);
        } finally {
            fclose($handle);
        }
    }

    /**
     * @return array{headers: list<string>, rows: list<array<string, string>>}
     */
    public static function fromFile(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('No se pudo abrir el archivo CSV.');
        }

        try {
            return self::fromStream($handle);
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param  resource  $handle
     * @return array{headers: list<string>, rows: list<array<string, string>>}
     */
    private static function fromStream($handle): array
    {
        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        if (! is_array($headers) || $headers === []) {
            return ['headers' => [], 'rows' => []];
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
        $headers = array_map(fn ($h) => trim((string) $h), $headers);
        $nHeaders = count($headers);

        $rows = [];
        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $data = array_slice($data, 0, $nHeaders);
            if (count($data) < $nHeaders) {
                $data = array_pad($data, $nHeaders, '');
            }
            $combined = array_combine($headers, array_map(fn ($v) => trim((string) $v), $data));
            if ($combined === false) {
                continue;
            }
            if (self::rowIsEmpty($combined)) {
                continue;
            }
            $rows[] = $combined;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * @param  array<string, string>  $row
     */
    private static function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== '') {
                return false;
            }
        }

        return true;
    }
}
