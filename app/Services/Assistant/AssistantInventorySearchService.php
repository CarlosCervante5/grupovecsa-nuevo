<?php

namespace App\Services\Assistant;

use App\Models\AssistantConversation;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AssistantInventorySearchService
{
    public function __construct(
        private readonly AssistantAdvisorAvailabilityService $advisorAvailability,
        private readonly AssistantCatalogPresenter $catalogPresenter
    ) {}

    public const SALES_ADVISOR_OFFER_MARKER = '¿Te gustaría que un asesor de ventas';

    private const MAX_RESULTS = 5;

    /** @var list<string> */
    private const INVENTORY_SIGNALS = [
        'disponib', 'inventario', 'tienen', 'tienes', 'hay', 'busco', 'buscar',
        'quiero', 'necesito', 'precio', 'costo', 'modelo', 'vehículo', 'vehiculo',
        'auto', 'coche', 'seminuevo', 'usado', 'nuevo', 'bmw', 'mini', 'motorrad',
        'serie', 'cooper', 'countryman', 'catalogo', 'catálogo', 'stock', 'unidades',
    ];

    /** @var list<string> */
    private const STOPWORDS = [
        'a', 'al', 'algo', 'con', 'de', 'del', 'el', 'en', 'es', 'esta', 'este',
        'la', 'las', 'le', 'lo', 'los', 'me', 'mi', 'mis', 'muy', 'na', 'no',
        'nos', 'o', 'para', 'por', 'que', 'qué', 'quiere', 'quisiera', 'saber',
        'se', 'si', 'sí', 'su', 'sus', 'te', 'tienen', 'tu', 'tus', 'un', 'una',
        'uno', 'usted', 'ver', 'y', 'ya', 'hay', 'disponible', 'disponibilidad',
        'informacion', 'información', 'favor', 'porfavor', 'gracias', 'hola',
        'buenas', 'buenos', 'dias', 'días', 'tardes', 'noches', 'podria', 'podría',
        'pueden', 'puede', 'puedo', 'tengo', 'gustaria', 'gustaría', 'interesa',
        'interesado', 'interesada', 'algun', 'algún', 'alguna', 'alguno',
    ];

    public function isInventoryQuery(string $text): bool
    {
        $lower = mb_strtolower(trim($text));
        if ($lower === '') {
            return false;
        }

        foreach (self::INVENTORY_SIGNALS as $signal) {
            if (str_contains($lower, $signal)) {
                return true;
            }
        }

        return (bool) preg_match('/\bx\s*\d{1,2}\b/ui', $lower)
            || (bool) preg_match('/\b\d\s*serie\b/ui', $lower);
    }

    /**
     * @return array{text: string, catalog_cards: list<array<string, mixed>>}|null
     */
    public function tryBuildReply(
        AssistantConversation $conversation,
        string $userText,
        ?string $pageUrl = null
    ): ?array {
        $pageUrl = $pageUrl ?? $conversation->page_url;

        if (! $this->shouldAttemptVehicleSearch($userText, $pageUrl)) {
            return null;
        }

        $keyword = $this->extractKeyword($userText);
        if ($keyword === '') {
            return null;
        }

        $conversation->loadMissing('dealership');
        $dealershipName = $conversation->dealership?->name ?? 'tu sucursal';
        $dealershipId = $conversation->dealership_id ? (int) $conversation->dealership_id : null;

        $local = $this->search($keyword, $dealershipId, $pageUrl);
        if ($local->isNotEmpty()) {
            return $this->formatFoundReply($local, $dealershipName, true);
        }

        $network = $dealershipId !== null
            ? $this->search($keyword, null, $pageUrl)
            : collect();

        if ($network->isNotEmpty()) {
            return $this->formatFoundReply($network, $dealershipName, false);
        }

        return [
            'text' => $this->formatNoResultsReply($dealershipName, $keyword, $dealershipId),
            'catalog_cards' => [],
        ];
    }

    public function userAcceptsSalesAdvisor(AssistantConversation $conversation, string $userText): bool
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
            && str_contains($lastAssistant, self::SALES_ADVISOR_OFFER_MARKER);
    }

    public function userExplicitlyRequestsAdvisor(string $userText): bool
    {
        $lower = mb_strtolower(trim($userText));
        $signals = [
            'asesor de ventas',
            'asesor ventas',
            'hablar con un asesor',
            'hablar con asesor',
            'hablar con alguien',
            'persona real',
            'agente de ventas',
            'vendedor',
            'con ventas',
        ];

        foreach ($signals as $signal) {
            if (str_contains($lower, $signal)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, Vehicle>
     */
    public function shouldAttemptVehicleSearch(string $userText, ?string $pageUrl): bool
    {
        if ($this->isInventoryQuery($userText)) {
            return true;
        }

        if ($this->isAutosTopic($pageUrl) || $this->isMotosTopic($pageUrl)) {
            return $this->extractKeyword($userText) !== ''
                || (bool) preg_match('/\bx\s*\d{1,2}\b/ui', mb_strtolower($userText))
                || (bool) preg_match('/\b\d\s*serie\b/ui', mb_strtolower($userText));
        }

        return false;
    }

    private function search(string $keyword, ?int $dealershipId, ?string $pageUrl = null): Collection
    {
        $parts = preg_split('/\s+/u', trim($keyword)) ?: [];
        $parts = array_values(array_filter($parts, fn (string $p) => strlen($p) >= 2));
        if ($parts === []) {
            $parts = [trim($keyword)];
        }

        $query = Vehicle::query()
            ->with(['brand', 'line', 'model', 'version', 'dealership', 'firstImage', 'images'])
            ->whereIn('page_status', ['active', 'inactive'])
            ->where(function (Builder $outer) use ($parts, $keyword) {
                $likeFull = '%'.$keyword.'%';
                $outer->where(function (Builder $q) use ($likeFull) {
                    $this->applyVehicleKeywordClause($q, $likeFull);
                });
                foreach ($parts as $part) {
                    $outer->orWhere(function (Builder $q) use ($part) {
                        $this->applyVehicleKeywordClause($q, '%'.$part.'%');
                    });
                }
            });

        if ($dealershipId !== null) {
            $query->where('dealership_id', $dealershipId);
        }

        if ($this->isMotosTopic($pageUrl)) {
            $query->whereHas('brand', fn (Builder $b) => $b->where('name', 'LIKE', '%Motorrad%'));
        } elseif ($this->isAutosTopic($pageUrl)) {
            $query->whereHas('brand', fn (Builder $b) => $b->where('name', 'NOT LIKE', '%Motorrad%'));
        }

        return $query
            ->orderByDesc('updated_at')
            ->limit(self::MAX_RESULTS)
            ->get();
    }

    private function applyVehicleKeywordClause(Builder $query, string $like): void
    {
        $query->where('name', 'LIKE', $like)
            ->orWhere('uuid', 'LIKE', $like)
            ->orWhereHas('model', fn (Builder $m) => $m->where('name', 'LIKE', $like))
            ->orWhereHas('line', fn (Builder $l) => $l->where('name', 'LIKE', $like))
            ->orWhereHas('brand', fn (Builder $b) => $b->where('name', 'LIKE', $like))
            ->orWhereHas('version', fn (Builder $v) => $v->where('name', 'LIKE', $like))
            ->orWhereHas('body', fn (Builder $b) => $b->where('name', 'LIKE', $like));
    }

    private function extractKeyword(string $text): string
    {
        $lower = mb_strtolower(trim($text));
        $tokens = preg_split('/[^a-z0-9áéíóúüñ]+/u', $lower) ?: [];
        $kept = [];

        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '') {
                continue;
            }
            if (strlen($token) < 2 && ! preg_match('/^\d$/u', $token)) {
                continue;
            }
            if (in_array($token, self::STOPWORDS, true)) {
                continue;
            }
            $kept[] = $token;
        }

        if ($kept === []) {
            return '';
        }

        return implode(' ', array_slice(array_values(array_unique($kept)), 0, 4));
    }

    /**
     * @param  Collection<int, Vehicle>  $vehicles
     * @return array{text: string, catalog_cards: list<array<string, mixed>>}
     */
    private function formatFoundReply(Collection $vehicles, string $dealershipName, bool $atSelectedDealership): array
    {
        $count = $vehicles->count();
        $intro = $atSelectedDealership
            ? "Encontré {$count} ".($count === 1 ? 'opción' : 'opciones')." en inventario en {$dealershipName}. Desliza para ver:"
            : "No hay coincidencias en {$dealershipName}, pero encontré {$count} "
                .($count === 1 ? 'opción' : 'opciones').' en otras sucursales. Desliza para ver:';

        return [
            'text' => $intro."\n\n¿Te gustaría más información de alguna unidad o comparar opciones?",
            'catalog_cards' => $this->catalogPresenter->vehicleCards($vehicles),
        ];
    }

    private function formatNoResultsReply(string $dealershipName, string $keyword, ?int $dealershipId): string
    {
        $base = 'No encontré vehículos con «'.$keyword.'» disponibles en el inventario publicado '
            ."de {$dealershipName} en este momento.";

        if ($dealershipId === null || ! $this->advisorAvailability->hasAvailableAdvisor($dealershipId)) {
            return $base."\n\n"
                .'Puedes seguir consultando con el asistente: indica marca, año o presupuesto y revisamos otras opciones.';
        }

        return $base."\n\n"
            .self::SALES_ADVISOR_OFFER_MARKER.' te ayude a revisar llegadas, reservas u opciones en otras sucursales? '
            .'Responde sí o cuéntame marca, año y presupuesto aproximado.';
    }

    private function vehicleLabel(Vehicle $vehicle): string
    {
        if (trim((string) $vehicle->name) !== '') {
            return trim((string) $vehicle->name);
        }

        $parts = array_filter([
            $vehicle->brand?->name,
            $vehicle->line?->name,
            $vehicle->model?->name,
            $vehicle->version?->name,
        ]);

        return $parts !== [] ? implode(' ', $parts) : 'Vehículo disponible';
    }

    private function vehicleDetails(Vehicle $vehicle): string
    {
        $bits = [];

        $year = $vehicle->model?->year;
        if ($year) {
            $bits[] = 'Año '.$year;
        }

        $price = $vehicle->offer_price ?: $vehicle->sale_price ?: $vehicle->list_price;
        if ($price) {
            $bits[] = 'Precio $'.number_format((float) $price, 0, '.', ',');
        }

        if ($vehicle->mileage) {
            $bits[] = number_format((int) $vehicle->mileage, 0, '.', ',').' km';
        }

        if ($vehicle->exterior_color) {
            $bits[] = 'Color '.ucfirst((string) $vehicle->exterior_color);
        }

        if ($vehicle->category) {
            $bits[] = ucfirst((string) $vehicle->category);
        }

        if (! $vehicle->relationLoaded('dealership')) {
            $vehicle->loadMissing('dealership');
        }
        if ($vehicle->dealership?->name) {
            $bits[] = 'Sucursal '.$vehicle->dealership->name;
        }

        return $bits !== [] ? implode(' · ', $bits) : 'Consulta disponibilidad con un asesor';
    }

    public function isMotosTopic(?string $pageUrl): bool
    {
        return $pageUrl !== null && (bool) preg_match('#/motorrad(?:/|$)#i', $pageUrl);
    }

    public function isAutosTopic(?string $pageUrl): bool
    {
        return $pageUrl !== null && (bool) preg_match('#/compra-tu-auto(?:/|$)#i', $pageUrl);
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
