<?php

namespace App\Services\Assistant;

use App\Models\AssistantConversation;
use App\Models\AssistantMessage;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use OpenAI\Laravel\Facades\OpenAI;

class AssistantChatService
{
    private const SYSTEM_PROMPT = 'Eres el asistente virtual de Grupo VECSA, concesionario autorizado de BMW, MINI y BMW Motorrad en México. '
        .'Ayudas a los clientes con información sobre vehículos, servicios, citas, boutique, rewards y sucursales. '
        .'Sucursales: BMW Puebla Angelópolis, BMW Pachuca, BMW Oaxaca, BMW Veracruz. '
        .'Responde de forma amable, concisa y profesional. Si no sabes algo, sugiere contactar al equipo. '
        .'Responde siempre en español.';

    public function chat(Request $request): array
    {
        $data = $request->validate([
            'message' => 'required|string|max:500',
            'conversation_uuid' => 'nullable|string|max:64',
            'session_key' => 'nullable|string|max:64',
            'page_url' => 'nullable|string|max:500',
        ]);

        $user = $this->resolveUser($request);
        $conversation = $this->resolveConversation($data, $request, $user);

        $userText = trim($data['message']);
        $reply = $this->generateReply($userText);

        $this->persistMessages($conversation, $userText, $reply, $data, $user);

        return [
            'reply' => $reply,
            'conversation_uuid' => $conversation->uuid,
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
                if ($user && ! $existing->user_id) {
                    $existing->update($this->visitorAttributes($user));
                }

                return $existing;
            }
        }

        $sessionKey = trim((string) ($data['session_key'] ?? ''));
        if ($sessionKey === '') {
            $sessionKey = (string) Str::uuid();
        }

        return AssistantConversation::create([
            'session_key' => Str::limit($sessionKey, 64, ''),
            'user_id' => $user?->id,
            'page_url' => isset($data['page_url']) ? Str::limit(trim((string) $data['page_url']), 500, '') : null,
            'ip_address' => $request->ip(),
            ...$this->visitorAttributes($user),
        ]);
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

    private function generateReply(string $userText): string
    {
        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
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
