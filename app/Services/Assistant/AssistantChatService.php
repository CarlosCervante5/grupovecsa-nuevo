<?php

namespace App\Services\Assistant;

use App\Models\AssistantConversation;
use App\Models\AssistantMessage;
use App\Models\Customer;
use App\Models\Dealership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
class AssistantChatService
{
    private const SYSTEM_PROMPT = 'Eres el asistente virtual de Grupo VECSA, concesionario autorizado de BMW, MINI y BMW Motorrad en México. '
        .'Ayudas a los clientes con información sobre vehículos, servicios, citas, boutique, rewards y sucursales. '
        .'Responde de forma amable, concisa y profesional. '
        .'Cuando recibas datos de inventario en el contexto, preséntalos con esos datos exactos; no inventes unidades ni precios. '
        .'Si el cliente pide más información, ofrece detalles útiles y concretos. '
        .'Responde siempre en español.';

    public function __construct(
        private readonly AssistantDealershipAssigner $dealershipAssigner,
        private readonly AssistantAdvisorAvailabilityService $advisorAvailability,
        private readonly AssistantLlmService $llm,
        private readonly AssistantInventorySearchService $inventory,
        private readonly AssistantBoutiqueSearchService $boutique,
        private readonly AssistantMessageCatalogCodec $catalogCodec,
        private readonly AssistantContactCallbackService $contactCallback
    ) {}

    /**
     * @return list<array{id: int, name: string, location: string|null, state: string|null}>
     */
    public function listDealershipsForChat(): array
    {
        return Dealership::query()
            ->orderBy('name')
            ->get(['id', 'name', 'location', 'state'])
            ->map(fn (Dealership $d) => [
                'id' => (int) $d->id,
                'name' => $d->name,
                'location' => $d->location,
                'state' => $d->state,
                'advisors_available' => $this->advisorAvailability->hasAvailableAdvisor((int) $d->id),
            ])
            ->filter(fn (array $row) => $row['advisors_available'])
            ->map(fn (array $row) => [
                'id' => $row['id'],
                'name' => $row['name'],
                'location' => $row['location'],
                'state' => $row['state'],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{id: int, name: string, location: string|null, state: string|null}>  $dealerships
     */
    public function formatDealershipPickerReply(array $dealerships): string
    {
        if ($dealerships === []) {
            return 'Por favor elige la sucursal con la que deseas contactar. '
                .'En este momento ninguna sucursal tiene asesores en línea; intenta de nuevo en unos minutos.';
        }

        $lines = ['Por favor elige la sucursal con la que deseas contactar:'];
        foreach ($dealerships as $index => $d) {
            $label = $d['name'];
            if (! empty($d['location'])) {
                $label .= ' — '.$d['location'];
            } elseif (! empty($d['state'])) {
                $label .= ' — '.$d['state'];
            }
            $lines[] = ($index + 1).'. '.$label;
        }

        return implode("\n", $lines);
    }

    public function chat(Request $request): array
    {
        $dealershipTable = (new Dealership)->getTable();

        $data = $request->validate([
            'message' => 'required|string|max:500',
            'conversation_uuid' => 'nullable|string|max:64',
            'session_key' => 'nullable|string|max:64',
            'page_url' => 'nullable|string|max:500',
            'chat_topic' => 'nullable|string|in:autos,motos,boutique,general',
            'dealership_id' => 'nullable|integer|exists:'.$dealershipTable.',id',
        ]);

        if (! empty($data['chat_topic']) && empty($data['page_url'])) {
            $data['page_url'] = $this->pageUrlForChatTopic((string) $data['chat_topic']);
        }

        $user = $this->resolveUser($request);

        if (empty($data['conversation_uuid']) && empty($data['dealership_id'])) {
            $dealerships = $this->listDealershipsForChat();

            return [
                'needs_dealership' => true,
                'dealerships' => $dealerships,
                'reply' => $this->formatDealershipPickerReply($dealerships),
            ];
        }

        $conversation = $this->resolveConversation($data, $request, $user);
        $conversation->refresh();
        $this->syncPageUrl($conversation, $data);

        $userText = trim($data['message']);
        $pageUrl = $this->resolvePageUrl($data, $conversation);

        if ($conversation->isHumanHandoff()) {
            $this->persistUserMessageOnly($conversation, $userText, $data, $user);

            return $this->chatPayload($conversation, '', true);
        }

        $callbackReply = $this->contactCallback->tryHandleMessage($conversation, $userText);
        if ($callbackReply !== null) {
            $this->persistMessages($conversation, $userText, $callbackReply['text'], $data, $user);

            return $this->chatPayload($conversation, $callbackReply['text'], false);
        }

        if ($this->isBoutiqueChat($pageUrl)) {
            if ($this->boutique->userAcceptsBoutiqueAdvisor($conversation, $userText)) {
                $reply = $this->activateSalesHandoff($conversation, 'boutique');
                $this->persistMessages($conversation, $userText, $reply, $data, $user);

                return $this->chatPayload($conversation, $reply, true);
            }
        } elseif ($this->inventory->userAcceptsSalesAdvisor($conversation, $userText)) {
            $reply = $this->activateSalesHandoff($conversation, 'ventas');
            $this->persistMessages($conversation, $userText, $reply, $data, $user);

            return $this->chatPayload($conversation, $reply, true);
        }

        $boutiqueReply = $this->boutique->tryBuildReply($conversation, $userText, $pageUrl);
        if ($boutiqueReply !== null) {
            $this->persistMessages(
                $conversation,
                $userText,
                $boutiqueReply['text'],
                $data,
                $user,
                $boutiqueReply['catalog_cards']
            );

            return $this->chatPayload(
                $conversation,
                $boutiqueReply['text'],
                false,
                $boutiqueReply['catalog_cards']
            );
        }

        if (! $this->isBoutiqueChat($pageUrl)) {
            $inventoryReply = $this->inventory->tryBuildReply($conversation, $userText, $pageUrl);
            if ($inventoryReply !== null) {
                $this->persistMessages(
                    $conversation,
                    $userText,
                    $inventoryReply['text'],
                    $data,
                    $user,
                    $inventoryReply['catalog_cards']
                );

                return $this->chatPayload(
                    $conversation,
                    $inventoryReply['text'],
                    false,
                    $inventoryReply['catalog_cards']
                );
            }
        }

        $reply = $this->generateReply($userText, $conversation);
        $this->persistMessages($conversation, $userText, $reply, $data, $user);

        return $this->chatPayload($conversation, $reply, false);
    }

    /**
     * @return array{messages: list<array{id: int, role: string, content: string, created_at: string|null}>}
     */
    public function pollMessages(Request $request): array
    {
        $data = $request->validate([
            'conversation_uuid' => 'required|string|max:64',
            'session_key' => 'required|string|max:64',
            'after_id' => 'nullable|integer|min:0',
            'mark_read' => 'sometimes|boolean',
            'last_read_message_id' => 'nullable|integer|min:0',
        ]);

        $conversation = AssistantConversation::findByUuid($data['conversation_uuid']);
        if (! $conversation || $conversation->session_key !== $data['session_key']) {
            throw ValidationException::withMessages([
                'conversation_uuid' => ['Conversación no válida.'],
            ]);
        }

        if (! empty($data['mark_read'])) {
            $readId = isset($data['last_read_message_id'])
                ? (int) $data['last_read_message_id']
                : null;
            $conversation->markVisitorRead($readId);
            $conversation->refresh();
        }

        $query = $conversation->messages();
        if (! empty($data['after_id'])) {
            $query->where('id', '>', (int) $data['after_id']);
        }

        $messages = $query->get(['id', 'role', 'content', 'created_at'])
            ->map(function (AssistantMessage $m) {
                $decoded = $this->catalogCodec->decode((string) $m->content);

                return [
                    'id' => (int) $m->id,
                    'role' => $m->role,
                    'content' => $decoded['text'],
                    'catalog_cards' => $decoded['catalog_cards'],
                    'created_at' => $m->created_at,
                ];
            })
            ->values()
            ->all();

        return [
            'messages' => $messages,
            'human_handoff' => $conversation->isHumanHandoff(),
            'conversation_uuid' => $conversation->uuid,
            'unread_count' => $conversation->countUnreadForVisitor(),
        ];
    }

    /**
     * @return array{unread_count: int, conversations_with_unread: int}
     */
    public function visitorUnreadSummary(string $conversationUuid, string $sessionKey): array
    {
        $conversation = AssistantConversation::findByUuid($conversationUuid);
        if (! $conversation || $conversation->session_key !== $sessionKey) {
            return ['unread_count' => 0, 'conversations_with_unread' => 0];
        }

        $unread = $conversation->countUnreadForVisitor();

        return [
            'unread_count' => $unread,
            'conversations_with_unread' => $unread > 0 ? 1 : 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @param  list<array<string, mixed>>  $catalogCards
     */
    private function chatPayload(
        AssistantConversation $conversation,
        string $reply,
        bool $humanHandoff,
        array $catalogCards = []
    ): array {
        $conversation->loadMissing('dealership');

        $payload = [
            'conversation_uuid' => $conversation->uuid,
            'dealership_id' => $conversation->dealership_id,
            'dealership_name' => $conversation->dealership?->name,
            'human_handoff' => $humanHandoff,
        ];

        if ($reply !== '') {
            $payload['reply'] = $reply;
            $lastAssistantId = $conversation->messages()
                ->where('role', 'assistant')
                ->orderByDesc('id')
                ->value('id');
            if ($lastAssistantId) {
                $payload['reply_message_id'] = (int) $lastAssistantId;
            }
        }

        if ($catalogCards !== []) {
            $payload['catalog_cards'] = $catalogCards;
        }

        return $payload;
    }

    private function isBoutiqueChat(?string $pageUrl): bool
    {
        return $this->boutique->isBoutiqueSection($pageUrl);
    }

    private function pageUrlForChatTopic(string $topic): string
    {
        return match ($topic) {
            'boutique' => '/boutique',
            'motos' => '/motorrad',
            'autos' => '/compra-tu-auto',
            default => '/',
        };
    }

    private function resolveUser(Request $request): ?User
    {
        $token = $request->bearerToken();
        if (! $token) {
            return null;
        }

        $access = PersonalAccessToken::findToken($token);

        return $access?->tokenable instanceof User ? $access->tokenable : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveConversation(array $data, Request $request, ?User $user): AssistantConversation
    {
        if (! empty($data['conversation_uuid'])) {
            $existing = AssistantConversation::findByUuid($data['conversation_uuid']);
            if ($existing) {
                $this->syncConversationMeta($existing, $data, $user);

                return $existing;
            }
        }

        $dealershipId = isset($data['dealership_id']) ? (int) $data['dealership_id'] : null;
        if (! $dealershipId) {
            throw ValidationException::withMessages([
                'dealership_id' => ['Selecciona una sucursal para iniciar la conversación.'],
            ]);
        }

        $sessionKey = trim((string) ($data['session_key'] ?? ''));
        if ($sessionKey === '') {
            $sessionKey = (string) Str::uuid();
        }

        $assignedUserId = $this->dealershipAssigner->assignUserIdForDealership($dealershipId);

        return AssistantConversation::create([
            'session_key' => Str::limit($sessionKey, 64, ''),
            'user_id' => $user?->id,
            'dealership_id' => $dealershipId,
            'assigned_user_id' => $assignedUserId,
            'page_url' => isset($data['page_url']) ? Str::limit(trim((string) $data['page_url']), 500, '') : null,
            'ip_address' => $request->ip(),
            ...$this->visitorAttributes($user),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncConversationMeta(AssistantConversation $conversation, array $data, ?User $user): void
    {
        $updates = [];

        if ($user && ! $conversation->user_id) {
            $updates['user_id'] = $user->id;
            $updates = array_merge($updates, $this->visitorAttributes($user));
        }

        if (! empty($data['dealership_id'])) {
            $dealershipId = (int) $data['dealership_id'];
            if ((int) $conversation->dealership_id !== $dealershipId) {
                $updates['dealership_id'] = $dealershipId;
                $updates['assigned_user_id'] = $this->dealershipAssigner->assignUserIdForDealership($dealershipId);
            } elseif (! $conversation->assigned_user_id) {
                $updates['assigned_user_id'] = $this->dealershipAssigner->assignUserIdForDealership($dealershipId);
            }
        }

        if ($updates !== []) {
            $conversation->update($updates);
            $conversation->refresh();
        }
    }

    /**
     * @return array{visitor_name: ?string, visitor_email: ?string}
     */
    private function visitorAttributes(?User $user): array
    {
        if (! $user) {
            return ['visitor_name' => null, 'visitor_email' => null];
        }

        $customer = Customer::query()->where('user_id', $user->id)->first();

        $fullName = trim(($customer?->name ?? '').' '.($customer?->last_name ?? ''));

        return [
            'visitor_name' => $fullName !== '' ? $fullName : ($user->nickname ?? null),
            'visitor_email' => $customer?->email_1 ?? $user->email ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncPageUrl(AssistantConversation $conversation, array $data): void
    {
        $url = null;
        if (! empty($data['page_url'])) {
            $url = Str::limit(trim((string) $data['page_url']), 500, '');
        } elseif (! empty($data['chat_topic'])) {
            $url = $this->pageUrlForChatTopic((string) $data['chat_topic']);
        }

        if ($url === null || $url === '') {
            return;
        }

        if ($conversation->page_url !== $url) {
            $conversation->update(['page_url' => $url]);
            $conversation->refresh();
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolvePageUrl(array $data, AssistantConversation $conversation): ?string
    {
        if (! empty($data['page_url'])) {
            return Str::limit(trim((string) $data['page_url']), 500, '');
        }

        return $conversation->page_url;
    }

    private function activateSalesHandoff(AssistantConversation $conversation, string $context = 'ventas'): string
    {
        $conversation->loadMissing('dealership');
        $dealershipName = $conversation->dealership?->name ?? 'Grupo VECSA';
        $dealershipId = (int) ($conversation->dealership_id ?? 0);

        if ($dealershipId > 0 && ! $this->advisorAvailability->hasAvailableAdvisor($dealershipId)) {
            return $this->contactCallback->beginCallbackRequest($conversation, $dealershipName);
        }

        if (! $conversation->isHumanHandoff()) {
            $assignedUserId = $conversation->assigned_user_id;
            if (! $assignedUserId && $dealershipId > 0) {
                $assignedUserId = $this->dealershipAssigner->assignSalesUserIdForDealership($dealershipId);
            }

            if (! $assignedUserId) {
                return $this->contactCallback->beginCallbackRequest($conversation, $dealershipName);
            }

            $conversation->update([
                'human_handoff_at' => now(),
                'assigned_user_id' => $assignedUserId,
            ]);
            $conversation->refresh();
        }

        if ($context === 'boutique') {
            return 'Perfecto. Un asesor de la boutique de '.$dealershipName
                .' te atenderá por este chat en breve. Si lo deseas, indica producto, marca y talla o modelo.';
        }

        return 'Perfecto. Un asesor de ventas de '.$dealershipName
            .' atenderá tu solicitud por este chat en breve. Si lo deseas, indica modelo, presupuesto y horario preferido de contacto.';
    }

    private function generateReply(string $userText, AssistantConversation $conversation): string
    {
        $system = self::SYSTEM_PROMPT;
        $conversation->loadMissing(['dealership', 'messages']);
        if ($conversation->dealership) {
            $system .= ' El cliente está en contacto con la sucursal '
                .$conversation->dealership->name
                .($conversation->dealership->location ? ' ('.$conversation->dealership->location.')' : '')
                .'.';
        }

        $pageUrl = $conversation->page_url;
        if ($this->isBoutiqueChat($pageUrl)) {
            $system .= ' El cliente navega la sección Boutique (accesorios y productos). '
                .'Prioriza preguntar producto y marca si aún no los ha indicado.';
        }

        if (! $this->isBoutiqueChat($pageUrl)) {
            $inventoryContext = $this->inventory->tryBuildReply($conversation, $userText, $pageUrl);
            if ($inventoryContext !== null) {
                $system .= "\n\nDatos de inventario verificados:\n".$inventoryContext['text'];
            }
        }

        $recent = $conversation->messages
            ->sortByDesc('id')
            ->take(12)
            ->sortBy('id')
            ->values();
        $history = AssistantLlmService::historyFromMessages($recent);

        $reply = $this->llm->generate($system, $userText, $history);

        if ($reply !== null && $reply !== '') {
            return $reply;
        }

        return 'No pude procesar tu mensaje: falta configurar la IA del chat en Panel desarrollo '
            .'(Gemini u OpenAI). Si el problema continúa, usa el botón para elegir sucursal y hablar con un asesor.';
    }

    /**
     * Solo guarda el mensaje del visitante cuando un asesor ya atiende la conversación.
     *
     * @param  array<string, mixed>  $data
     */
    private function persistUserMessageOnly(
        AssistantConversation $conversation,
        string $userText,
        array $data,
        ?User $user
    ): void {
        AssistantMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $userText,
        ]);

        $updates = [
            'messages_count' => (int) $conversation->messages_count + 1,
            'last_message_at' => now(),
        ];

        if (! $conversation->preview) {
            $updates['preview'] = Str::limit($userText, 500, '');
        }

        if (empty($conversation->page_url) && ! empty($data['page_url'])) {
            $updates['page_url'] = Str::limit(trim((string) $data['page_url']), 500, '');
        }

        if ($user && ! $conversation->user_id) {
            $updates['user_id'] = $user->id;
            $updates = array_merge($updates, $this->visitorAttributes($user));
        }

        $conversation->update($updates);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    /**
     * @param  list<array<string, mixed>>  $catalogCards
     */
    private function persistMessages(
        AssistantConversation $conversation,
        string $userText,
        string $reply,
        array $data,
        ?User $user,
        array $catalogCards = []
    ): void {
        AssistantMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $userText,
        ]);

        AssistantMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $this->catalogCodec->encode($reply, $catalogCards),
        ]);

        $updates = [
            'messages_count' => (int) $conversation->messages_count + 2,
            'last_message_at' => now(),
        ];

        if (! $conversation->preview) {
            $updates['preview'] = Str::limit($userText, 500, '');
        }

        if (empty($conversation->page_url) && ! empty($data['page_url'])) {
            $updates['page_url'] = Str::limit(trim((string) $data['page_url']), 500, '');
        }

        if ($user && ! $conversation->user_id) {
            $updates['user_id'] = $user->id;
            $updates = array_merge($updates, $this->visitorAttributes($user));
        }

        $conversation->update($updates);
    }
}
