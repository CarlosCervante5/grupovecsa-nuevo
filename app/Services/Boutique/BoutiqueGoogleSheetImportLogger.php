<?php

namespace App\Services\Boutique;

/**
 * Registro de incidencias durante importación desde Google Sheets (imágenes, filas).
 */
final class BoutiqueGoogleSheetImportLogger
{
    /** @var list<array{type: string, sku: string, url: string|null, message: string, hint: string|null}> */
    private array $entries = [];

    public function __construct(private readonly int $maxEntries = 150) {}

    public function add(
        string $type,
        string $sku,
        string $message,
        ?string $url = null,
        ?string $hint = null
    ): void {
        if (count($this->entries) >= $this->maxEntries) {
            return;
        }

        $this->entries[] = [
            'type' => $type,
            'sku' => trim($sku) !== '' ? trim($sku) : '—',
            'url' => $url !== null && trim($url) !== '' ? trim($url) : null,
            'message' => $message,
            'hint' => $hint !== null && trim($hint) !== '' ? trim($hint) : null,
        ];
    }

    /**
     * @return list<array{type: string, sku: string, url: string|null, message: string, hint: string|null}>
     */
    public function entries(): array
    {
        return $this->entries;
    }

    public function count(): int
    {
        return count($this->entries);
    }

    public function isTruncated(): bool
    {
        return count($this->entries) >= $this->maxEntries;
    }
}
