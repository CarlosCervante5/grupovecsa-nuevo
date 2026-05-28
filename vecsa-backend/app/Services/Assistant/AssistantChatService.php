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
use OpenAI\Laravel\Facades\OpenAI;

class AssistantChatService
{
    private const SYSTEM_PROMPT = 'Eres el asistente virtual de Grupo VECSA, concesionario autorizado de BMW, MINI y BMW Motorrad en México. '
        .'Ayudas a los clientes con información sobre vehículos, servicios, citas, boutique, rewards y sucursales. '
        .'Responde de forma amable, concisa y profesional. Si no sabes algo, sugiere contactar al equipo de la sucursal. '
        .'Responde siempre en español.';

    public function __construct(
        private readonly AssistantDealershipAssigner $dealershipAssigner
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
            return 'Por favor elige la sucursal con la que deseas contactar. En este momento no hay sucursales disponibles en el sistema; intenta más tarde.';
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
            'dealership_id' => 'nullable|integer|exists:'.$dealershipTable.',id',
        ]);

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

        $userText = trim($data['message']);

        if ($conversation->isHumanHandoff()) {
            $reply = 'Tu mensaje fue enviado al asesor. Te responderá en breve.';
            $this->persistMessages($conversation, $userText, $reply, $data, $user);

            return $this->chatPayload($conversation, $reply, true);
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
        ]);

        $conversation = AssistantConversation::findByUuid($data['conversation_uuid']);
        if (! $conversation || $conversation->session_key !== $data['session_key']) {
            throw ValidationException::withMessages([
                'conversation_uuid' => ['Conversación no válida.'],
            ]);
        }

        $query = $conversation->messages();
        if (! empty($data['after_id'])) {
            $query->where('id', '>', (int) $data['after_id']);
        }

        $messages = $query->get(['id', 'role', 'content', 'created_at'])
            ->map(fn (AssistantMessage $m) => [
                'id' => (int) $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'created_at' => $m->created_at,
            ])
            ->values()
            ->all();

        return [
            'messages' => $messages,
            'human_handoff' => $conversation->isHumanHandoff(),
            'conversation_uuid' => $conversation->uuid,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function chatPayload(AssistantConversation $conversation, string $reply, bool $humanHandoff): array
    {
        $conversation->loadMissing('dealership');

        return [
            'reply' => $reply,
            'conversation_uuid' => $conversation->uuid,
            'dealership_id' => $conversation->dealership_id,
            'dealership_name' => $conversation->dealership?->name,
            'human_handoff' => $humanHandoff,
        ];
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

        if (! $conversation->dealership_id && ! empty($data['dealership_id'])) {
            $dealershipId = (int) $data['dealership_id'];
            $updates['dealership_id'] = $dealershipId;
            if (! $conversation->assigned_user_id) {
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

    private function generateReply(string $userText, AssistantConversation $conversation): string
    {
        $system = self::SYSTEM_PROMPT;
        $conversation->loadMissing('dealership');
        if ($conversation->dealership) {
            $system .= ' El cliente está en contacto con la sucursal '
                .$conversation->dealership->name
                .($conversation->dealership->location ? ' ('.$conversation->dealership->location.')' : '')
                .'.';
        }

        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $userText],
                ],
                'max_tokens' => 300,
                'temperature' => 0.7,
            ]);

            return trim((string) ($result->choices[0]->message->content ?? ''))
                ?: 'Lo siento, no pude generar una respuesta. Intenta de nuevo.';
        } catch (\Throwable) {
            return 'Lo siento, no pude procesar tu mensaje en este momento. Intenta de nuevo más tarde.';
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function persistMessages(
        AssistantConversation $conversation,
        string $userText,
        string $reply,
        array $data,
        ?User $user
    ): void {
        AssistantMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $userText,
        ]);

        AssistantMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $reply,
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
