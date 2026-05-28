<?php

namespace App\Services\Assistant;

use App\Models\AssistantConversation;
use App\Models\Boutique\BoutiqueProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AssistantBoutiqueSearchService
{
    public function __construct(
        private readonly AssistantAdvisorAvailabilityService $advisorAvailability,
        private readonly AssistantCatalogPresenter $catalogPresenter
    ) {}

    public const PRODUCT_BRAND_PROMPT_MARKER = '¿Qué producto y marca estás buscando';

    public const BOUTIQUE_ADVISOR_OFFER_MARKER = '¿Te gustaría que un asesor de la boutique';

    private const MAX_RESULTS = 5;

    /** @var list<string> */
    private const GREETINGS = [
        'hola', 'buenas', 'buenos', 'hey', 'saludos', 'hi', 'hello', 'qué tal', 'que tal',
    ];

    /** @var list<string> */
    private const STOPWORDS = [
        'a', 'al', 'algo', 'busco', 'buscar', 'con', 'de', 'del', 'el', 'en', 'es', 'esta',
        'este', 'la', 'las', 'lo', 'los', 'me', 'mi', 'necesito', 'para', 'por', 'que',
        'qué', 'quiero', 'un', 'una', 'uno', 'y', 'en', 'favor', 'gracias', 'hola',
        'producto', 'productos', 'marca', 'marcas', 'boutique', 'tienda', 'articulo',
        'artículo', 'articulos', 'artículos',
    ];

    public function isBoutiqueSection(?string $pageUrl): bool
    {
        if ($pageUrl === null || trim($pageUrl) === '') {
            return false;
        }

        return (bool) preg_match('#/boutique(?:/|$)#i', $pageUrl);
    }

    /**
     * @return array{text: string, catalog_cards: list<array<string, mixed>>}|null
     */
    public function tryBuildReply(
        AssistantConversation $conversation,
        string $userText,
        ?string $pageUrl
    ): ?array {
        if (! $this->isBoutiqueSection($pageUrl)) {
            return null;
        }

        $terms = $this->extractSearchTerms($userText);

        if ($this->shouldAskProductAndBrand($conversation, $userText, $terms)) {
            return ['text' => $this->productBrandPrompt(), 'catalog_cards' => []];
        }

        if ($terms === '') {
            return ['text' => $this->productBrandPrompt(), 'catalog_cards' => []];
        }

        $dealershipId = $conversation->dealership_id ? (int) $conversation->dealership_id : null;
        $products = $this->search($terms, $dealershipId);

        if ($products->isEmpty() && $dealershipId !== null) {
            $products = $this->search($terms, null);
        }

        if ($products->isNotEmpty()) {
            $conversation->loadMissing('dealership');
            $dealershipName = $conversation->dealership?->name ?? 'Boutique VECSA';

            return $this->formatFoundReply($products, $dealershipName);
        }

        return [
            'text' => $this->formatNoResultsReply($terms, $dealershipId),
            'catalog_cards' => [],
        ];
    }

    public function userAcceptsBoutiqueAdvisor(AssistantConversation $conversation, string $userText): bool
    {
        if ($this->userExplicitlyRequestsAdvisor($userText)) {
            return true;
        }

        if (! $this->isAffirmative($userText)) {
            return false;
        }

        $lastAssistant = $conversation->messages()
            ->where('role', 'assistant')
            ->orderByDesc('id')
            ->value('content');

        return is_string($lastAssistant)
            && str_contains($lastAssistant, self::BOUTIQUE_ADVISOR_OFFER_MARKER);
    }

    public function userExplicitlyRequestsAdvisor(string $userText): bool
    {
        $lower = mb_strtolower(trim($userText));
        $signals = [
            'asesor de boutique',
            'asesor boutique',
            'hablar con alguien',
            'persona real',
            'ayuda humana',
            'atención personal',
            'atencion personal',
            'hablar con un asesor',
            'hablar con asesor',
        ];

        foreach ($signals as $signal) {
            if (str_contains($lower, $signal)) {
                return true;
            }
        }

        return (bool) preg_match('/\basesor\b/u', $lower)
            && ! str_contains($lower, 'asesoramiento');
    }

    private function shouldAskProductAndBrand(
        AssistantConversation $conversation,
        string $userText,
        string $terms = ''
    ): bool {
        $trimmed = trim($userText);
        if ($trimmed === '' || $this->isGreeting($trimmed)) {
            return true;
        }

        if ($terms !== '') {
            return false;
        }

        $lastAssistant = $conversation->messages()
            ->where('role', 'assistant')
            ->orderByDesc('id')
            ->value('content');

        return ! (is_string($lastAssistant)
            && str_contains($lastAssistant, self::PRODUCT_BRAND_PROMPT_MARKER));
    }

    private function productBrandPrompt(): string
    {
        return '¡Hola! Veo que estás en la Boutique VECSA. '
            .self::PRODUCT_BRAND_PROMPT_MARKER.'? '
            .'Por ejemplo: «maleta BMW», «chaleco MINI» o «accesorio Motorrad».';
    }

    /**
     * @return Collection<int, BoutiqueProduct>
     */
    private function search(string $terms, ?int $dealershipId): Collection
    {
        $parts = preg_split('/\s+/u', $terms) ?: [];
        $parts = array_values(array_filter($parts, fn (string $p) => strlen($p) >= 2));

        $query = BoutiqueProduct::query()
            ->with([
                'category',
                'dealership',
                'images' => fn ($q) => $q->where('status', 'uploaded')->orderBy('sort_id'),
            ])
            ->published();

        if ($dealershipId !== null) {
            $query->where(function (Builder $q) use ($dealershipId) {
                $q->where('dealership_id', $dealershipId)
                    ->orWhereNull('dealership_id');
            });
        }

        $query->where(function (Builder $outer) use ($parts, $terms) {
            $likeFull = '%'.$terms.'%';
            $outer->where('name', 'LIKE', $likeFull)
                ->orWhere('description', 'LIKE', $likeFull)
                ->orWhere('sku', 'LIKE', $likeFull)
                ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'LIKE', $likeFull));

            foreach ($parts as $part) {
                $like = '%'.$part.'%';
                $outer->orWhere('name', 'LIKE', $like)
                    ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'LIKE', $like));
            }
        });

        return $query
            ->orderByDesc('updated_at')
            ->limit(self::MAX_RESULTS)
            ->get();
    }

    private function extractSearchTerms(string $text): string
    {
        $lower = mb_strtolower(trim($text));

        if (preg_match('/marca\s+([^,.;]+)/u', $lower, $brandMatch)) {
            $brand = trim($brandMatch[1]);
            if (preg_match('/producto\s+([^,.;]+)/u', $lower, $productMatch)) {
                return trim($productMatch[1].' '.$brand);
            }

            return $brand;
        }

        if (preg_match('/producto\s+([^,.;]+)/u', $lower, $productMatch)) {
            return trim($productMatch[1]);
        }

        $tokens = preg_split('/[^a-z0-9áéíóúüñ]+/u', $lower) ?: [];
        $kept = [];

        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '' || strlen($token) < 2) {
                continue;
            }
            if (in_array($token, self::STOPWORDS, true)) {
                continue;
            }
            $kept[] = $token;
        }

        return implode(' ', array_values(array_unique($kept)));
    }

    /**
     * @param  Collection<int, BoutiqueProduct>  $products
     * @return array{text: string, catalog_cards: list<array<string, mixed>>}
     */
    private function formatFoundReply(Collection $products, string $dealershipName): array
    {
        $count = $products->count();

        return [
            'text' => "Encontré {$count} ".($count === 1 ? 'producto' : 'productos')
                ." en la Boutique ({$dealershipName}). Desliza para ver:\n\n"
                .'¿Quieres detalles de alguno o buscar otra marca o producto?',
            'catalog_cards' => $this->catalogPresenter->boutiqueProductCards($products),
        ];
    }

    private function formatNoResultsReply(string $terms, ?int $dealershipId): string
    {
        $base = 'No encontré productos con «'.$terms.'» en la Boutique en este momento.';

        if ($dealershipId === null || ! $this->advisorAvailability->hasAvailableAdvisor($dealershipId)) {
            return $base."\n\n"
                .'Puedes indicar otra marca o producto y seguimos buscando en el catálogo.';
        }

        return $base."\n\n"
            .self::BOUTIQUE_ADVISOR_OFFER_MARKER.' te ayude a ubicarlo o avisarte cuando haya existencia? '
            .'Responde sí o indica otra marca o producto.';
    }

    private function isGreeting(string $text): bool
    {
        $lower = mb_strtolower(trim($text));
        foreach (self::GREETINGS as $greeting) {
            if ($lower === $greeting || str_starts_with($lower, $greeting.' ')) {
                return true;
            }
        }

        return false;
    }

    private function isAffirmative(string $text): bool
    {
        $lower = mb_strtolower(trim($text));

        return (bool) preg_match(
            '/^(s[ií]|sip|claro|adelante|ok|vale|por favor|yes|yep|de acuerdo|est[aá] bien|bueno)\b/u',
            $lower
        );
    }
}
