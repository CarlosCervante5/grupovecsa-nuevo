<?php

namespace App\Services\Assistant;

class AssistantMessageCatalogCodec
{
    private const PREFIX = '@@VECSA_CATALOG@@';

    private const SUFFIX = '@@END_CATALOG@@';

    /**
     * @param  list<array<string, mixed>>  $catalogCards
     */
    public function encode(string $text, array $catalogCards = []): string
    {
        if ($catalogCards === []) {
            return $text;
        }

        $payload = json_encode([
            'text' => $text,
            'catalog_cards' => $catalogCards,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return self::PREFIX.$payload.self::SUFFIX;
    }

    /**
     * @return array{text: string, catalog_cards: list<array<string, mixed>>}
     */
    public function decode(string $content): array
    {
        $trimmed = trim($content);
        if (! str_starts_with($trimmed, self::PREFIX)) {
            return ['text' => $content, 'catalog_cards' => []];
        }

        $end = strpos($trimmed, self::SUFFIX);
        if ($end === false) {
            return ['text' => $content, 'catalog_cards' => []];
        }

        $json = substr($trimmed, strlen(self::PREFIX), $end - strlen(self::PREFIX));
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return ['text' => $content, 'catalog_cards' => []];
        }

        $text = is_string($data['text'] ?? null) ? $data['text'] : '';
        $cards = is_array($data['catalog_cards'] ?? null) ? $data['catalog_cards'] : [];

        return [
            'text' => $text,
            'catalog_cards' => array_values(array_filter($cards, 'is_array')),
        ];
    }
}
