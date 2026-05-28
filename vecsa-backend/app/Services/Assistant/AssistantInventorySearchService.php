<?php

namespace App\Services\Assistant;

use App\Models\AssistantConversation;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AssistantInventorySearchService
{
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
     * Respuesta con inventario o invitación a asesor; null si no aplica búsqueda.
     */
    public function tryBuildReply(AssistantConversation $conversation, string $userText): ?string
    {
        if (! $this->isInventoryQuery($userText)) {
            return null;
        }

        $keyword = $this->extractKeyword($userText);
        if ($keyword === '') {
            return null;
        }

        $conversation->loadMissing('dealership');
        $dealershipName = $conversation->dealership?->name ?? 'tu sucursal';
        $dealershipId = $conversation->dealership_id ? (int) $conversation->dealership_id : null;

        $local = $this->search($keyword, $dealershipId);
        if ($local->isNotEmpty()) {
            return $this->formatFoundReply($local, $dealershipName, true);
        }

        $network = $dealershipId !== null
            ? $this->search($keyword, null)
            : collect();

        if ($network->isNotEmpty()) {
            return $this->formatFoundReply($network, $dealershipName, false);
        }

        return $this->formatNoResultsReply($dealershipName, $keyword);
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
    private function search(string $keyword, ?int $dealershipId): Collection
    {
        $like = '%'.$keyword.'%';

        $query = Vehicle::query()
            ->with(['brand', 'line', 'model', 'version', 'dealership'])
            ->where('page_status', 'active')
            ->whereHas('images')
            ->where(function (Builder $q) use ($like) {
                $q->where('name', 'LIKE', $like)
                    ->orWhere('uuid', 'LIKE', $like)
                    ->orWhereHas('model', fn (Builder $m) => $m->where('name', 'LIKE', $like))
                    ->orWhereHas('line', fn (Builder $l) => $l->where('name', 'LIKE', $like))
                    ->orWhereHas('brand', fn (Builder $b) => $b->where('name', 'LIKE', $like))
                    ->orWhereHas('version', fn (Builder $v) => $v->where('name', 'LIKE', $like))
                    ->orWhereHas('body', fn (Builder $b) => $b->where('name', 'LIKE', $like));
            });

        if ($dealershipId !== null) {
            $query->where('dealership_id', $dealershipId);
        }

        return $query
            ->orderByDesc('updated_at')
            ->limit(self::MAX_RESULTS)
            ->get();
    }

    private function extractKeyword(string $text): string
    {
        $lower = mb_strtolower(trim($text));
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

        if ($kept === []) {
            return '';
        }

        return implode(' ', array_slice(array_values(array_unique($kept)), 0, 4));
    }

    /**
     * @param  Collection<int, Vehicle>  $vehicles
     */
    private function formatFoundReply(Collection $vehicles, string $dealershipName, bool $atSelectedDealership): string
    {
        $count = $vehicles->count();
        $intro = $atSelectedDealership
            ? "Encontré {$count} ".($count === 1 ? 'opción' : 'opciones')." en inventario en {$dealershipName}:"
            : "No hay coincidencias en {$dealershipName}, pero encontré {$count} "
                .($count === 1 ? 'opción' : 'opciones').' en otras sucursales de Grupo VECSA:';

        $lines = [$intro, ''];

        foreach ($vehicles as $index => $vehicle) {
            $lines[] = ($index + 1).'. '.$this->vehicleLabel($vehicle);
            $lines[] = '   '.$this->vehicleDetails($vehicle);
            $lines[] = '   Ver detalle: /compra-tu-auto/detail/'.$vehicle->uuid;
            $lines[] = '';
        }

        $lines[] = '¿Te gustaría más información de alguna unidad o comparar opciones?';

        return trim(implode("\n", $lines));
    }

    private function formatNoResultsReply(string $dealershipName, string $keyword): string
    {
        return 'No encontré vehículos con «'.$keyword.'» disponibles en el inventario publicado '
            ."de {$dealershipName} en este momento.\n\n"
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

    private function isAffirmative(string $text): bool
    {
        $lower = mb_strtolower(trim($text));

        return (bool) preg_match(
            '/^(s[ií]|sip|claro|adelante|ok|vale|por favor|yes|yep|de acuerdo|est[aá] bien|bueno)\b/u',
            $lower
        );
    }
}
