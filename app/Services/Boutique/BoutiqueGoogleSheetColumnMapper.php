<?php

namespace App\Services\Boutique;

class BoutiqueGoogleSheetColumnMapper
{
    /**
     * @return list<string>
     */
    public function canonicalKeys(): array
    {
        return array_keys($this->aliases());
    }

    /**
     * @return array<string, array{label: string, required: bool, example: string}>
     */
    public function fieldDefinitions(): array
    {
        $defs = [];
        foreach (BoutiqueGoogleSheetSyncService::columnCatalog() as $col) {
            $defs[$col['key']] = [
                'label' => $col['label'],
                'required' => (bool) $col['required'],
                'example' => $col['example'],
            ];
        }

        return $defs;
    }

    /**
     * @param  list<string>  $sheetHeaders
     * @return array<string, string|null> canonical => sheet header or null
     */
    public function suggestMapping(array $sheetHeaders): array
    {
        $used = [];
        $suggested = [];
        foreach ($this->canonicalKeys() as $canonical) {
            $match = $this->findHeaderForCanonical($sheetHeaders, $canonical, $used);
            $suggested[$canonical] = $match;
            if ($match !== null) {
                $used[$match] = true;
            }
        }

        return $suggested;
    }

    /**
     * @param  array<string, string|null|mixed>  $userMapping  canonical => sheet header
     * @param  list<string>  $sheetHeaders
     * @return array{mapping: array<string, string>, missing_required: list<string>, unmapped_headers: list<string>}
     */
    public function resolveMapping(array $userMapping, array $sheetHeaders): array
    {
        $headerSet = array_fill_keys($sheetHeaders, true);
        $mapping = [];

        foreach ($this->canonicalKeys() as $canonical) {
            $chosen = trim((string) ($userMapping[$canonical] ?? ''));
            if ($chosen === '' || ! isset($headerSet[$chosen])) {
                continue;
            }
            $mapping[$canonical] = $chosen;
        }

        $missingRequired = [];
        foreach ($this->fieldDefinitions() as $key => $def) {
            if ($def['required'] && ! isset($mapping[$key])) {
                $missingRequired[] = $key;
            }
        }

        $mappedHeaders = array_flip($mapping);
        $unmapped = [];
        foreach ($sheetHeaders as $header) {
            if (! isset($mappedHeaders[$header])) {
                $unmapped[] = $header;
            }
        }

        return [
            'mapping' => $mapping,
            'missing_required' => $missingRequired,
            'unmapped_headers' => $unmapped,
        ];
    }

    /**
     * @param  array<string, string>  $rawRow  header => value
     * @param  array<string, string>  $mapping  canonical => sheet header
     * @return array<string, string>
     */
    public function mapRow(array $rawRow, array $mapping): array
    {
        $out = [];
        foreach ($mapping as $canonical => $header) {
            if ($header === '' || ! array_key_exists($header, $rawRow)) {
                continue;
            }
            $value = trim((string) $rawRow[$header]);
            if ($value !== '') {
                $out[$canonical] = $value;
            }
        }

        if (isset($out['sku'])) {
            $out['sku'] = trim($out['sku']);
        }

        return $out;
    }

    /**
     * @param  list<array<string, string>>  $rawRows
     * @param  array<string, string>  $mapping
     * @return list<array<string, string>>
     */
    public function mapRows(array $rawRows, array $mapping): array
    {
        return array_map(fn (array $row) => $this->mapRow($row, $mapping), $rawRows);
    }

    /**
     * @param  list<string>  $sheetHeaders
     * @param  list<array<string, string>>  $rawRows
     * @return list<array<string, string>>
     */
    public function sampleRawRows(array $sheetHeaders, array $rawRows, int $limit = 3): array
    {
        $samples = [];
        foreach ($rawRows as $row) {
            $pick = [];
            foreach ($sheetHeaders as $header) {
                $pick[$header] = $row[$header] ?? '';
            }
            $samples[] = $pick;
            if (count($samples) >= $limit) {
                break;
            }
        }

        return $samples;
    }

    /**
     * @param  list<string>  $sheetHeaders
     * @param  list<string>  $used
     */
    private function findHeaderForCanonical(array $sheetHeaders, string $canonical, array &$used): ?string
    {
        $aliases = $this->aliases()[$canonical] ?? [];

        foreach ($sheetHeaders as $header) {
            if (isset($used[$header])) {
                continue;
            }
            $normalized = mb_strtolower(trim($header));
            foreach ($aliases as $alias) {
                if ($normalized === mb_strtolower($alias)) {
                    $used[$header] = true;

                    return $header;
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, list<string>>
     */
    private function aliases(): array
    {
        return BoutiqueGoogleSheetSyncService::columnAliases();
    }
}
