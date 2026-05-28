<?php

namespace App\Services\Assistant;

use App\Models\AssistantMessage;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenAI;

class AssistantLlmService
{
    private const GEMINI_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    public const DEFAULT_GEMINI_MODEL = 'gemini-2.0-flash';

    public const DEFAULT_OPENAI_MODEL = 'gpt-4o-mini';

    /**
     * @param  list<array{role: string, content: string}>  $history  Sin el mensaje actual del usuario
     */
    public function generate(string $systemPrompt, string $userText, array $history = []): ?string
    {
        if (! $this->isEnabled()) {
            return 'El asistente virtual no está disponible en este momento. Intenta más tarde.';
        }

        $order = $this->providerOrder();
        $lastError = '';

        foreach ($order as $provider) {
            try {
                $reply = $provider === 'gemini'
                    ? $this->generateWithGemini($systemPrompt, $userText, $history)
                    : $this->generateWithOpenAi($systemPrompt, $userText, $history);

                if ($reply !== null && $reply !== '') {
                    return $reply;
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::warning('Assistant LLM provider failed', [
                    'provider' => $provider,
                    'message' => $lastError,
                ]);
            }
        }

        if ($lastError !== '') {
            Log::error('Assistant LLM: all providers failed', ['last_error' => $lastError]);
        }

        return null;
    }

    public function isEnabled(): bool
    {
        return filter_var(SystemSetting::get('assistant_chat_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return list<string>
     */
    private function providerOrder(): array
    {
        $configured = strtolower(trim((string) SystemSetting::get('assistant_chat_provider', 'gemini')));

        return match ($configured) {
            'openai' => ['openai'],
            'auto' => ['gemini', 'openai'],
            default => ['gemini'],
        };
    }

    /**
     * @param  list<array{role: string, content: string}>  $history
     */
    private function generateWithGemini(string $systemPrompt, string $userText, array $history): ?string
    {
        $apiKey = $this->geminiApiKey();
        if ($apiKey === '') {
            throw new \RuntimeException('Falta GEMINI_API_KEY en Panel desarrollo.');
        }

        $model = trim((string) SystemSetting::get('assistant_chat_gemini_model', ''));
        if ($model === '') {
            $model = self::DEFAULT_GEMINI_MODEL;
        }

        $contents = [];
        foreach ($history as $msg) {
            $role = $msg['role'] === 'user' ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content']]],
            ];
        }
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userText]],
        ];

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 512,
            ],
        ];

        $url = sprintf(self::GEMINI_ENDPOINT, rawurlencode($model));
        $response = Http::timeout(45)
            ->withQueryParameters(['key' => $apiKey])
            ->acceptJson()
            ->post($url, $payload);

        if (! $response->successful()) {
            $hint = $this->formatGeminiError($response->json(), $response->body());
            throw new \RuntimeException('Gemini HTTP '.$response->status().': '.$hint);
        }

        $text = $this->extractGeminiText($response->json());
        if ($text === '') {
            throw new \RuntimeException('Gemini no devolvió texto.');
        }

        return $text;
    }

    /**
     * @param  list<array{role: string, content: string}>  $history
     */
    private function generateWithOpenAi(string $systemPrompt, string $userText, array $history): ?string
    {
        $apiKey = $this->openAiApiKey();
        if ($apiKey === '' || str_contains($apiKey, 'your-api-key')) {
            throw new \RuntimeException('Falta OPENAI_API_KEY en Panel desarrollo o .env.');
        }

        $model = trim((string) SystemSetting::get('assistant_chat_openai_model', ''));
        if ($model === '') {
            $model = self::DEFAULT_OPENAI_MODEL;
        }

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($history as $msg) {
            $role = $msg['role'] === 'user' ? 'user' : 'assistant';
            $messages[] = ['role' => $role, 'content' => $msg['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $userText];

        $client = OpenAI::factory()
            ->withApiKey($apiKey)
            ->make();

        $result = $client->chat()->create([
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => 512,
            'temperature' => 0.7,
        ]);

        return trim((string) ($result->choices[0]->message->content ?? '')) ?: null;
    }

    private function geminiApiKey(): string
    {
        $dedicated = trim((string) SystemSetting::getEncrypted('assistant_gemini_api_key', ''));
        if ($dedicated !== '') {
            return $dedicated;
        }

        return trim((string) SystemSetting::getEncrypted('gemini_api_key', ''));
    }

    private function openAiApiKey(): string
    {
        $fromDb = trim((string) SystemSetting::getEncrypted('openai_api_key', ''));
        if ($fromDb !== '') {
            return $fromDb;
        }

        return trim((string) config('openai.api_key', env('OPENAI_API_KEY', '')));
    }

    /**
     * @param  list<AssistantMessage>  $recentMessages
     * @return list<array{role: string, content: string}>
     */
    public static function historyFromMessages(iterable $recentMessages): array
    {
        $history = [];
        foreach ($recentMessages as $message) {
            if (! $message instanceof AssistantMessage) {
                continue;
            }
            $role = $message->role === 'user' ? 'user' : 'assistant';
            if ($message->role === 'agent') {
                $role = 'assistant';
            }
            $history[] = [
                'role' => $role,
                'content' => (string) $message->content,
            ];
        }

        return $history;
    }

    private function extractGeminiText(?array $json): string
    {
        $candidates = $json['candidates'] ?? [];
        if (! is_array($candidates) || $candidates === []) {
            return '';
        }
        $parts = $candidates[0]['content']['parts'] ?? [];
        $chunks = [];
        foreach ($parts as $part) {
            if (isset($part['text']) && is_string($part['text'])) {
                $chunks[] = $part['text'];
            }
        }

        return trim(implode("\n", $chunks));
    }

    private function formatGeminiError(?array $json, string $rawBody): string
    {
        $msg = $json['error']['message'] ?? null;
        if (is_string($msg) && $msg !== '') {
            return $msg;
        }

        return Str::limit(trim($rawBody), 200, '');
    }
}
