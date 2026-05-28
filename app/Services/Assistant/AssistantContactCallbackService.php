<?php

namespace App\Services\Assistant;

use App\Models\AssistantConversation;

class AssistantContactCallbackService
{
    public function isPending(AssistantConversation $conversation): bool
    {
        return $conversation->contact_callback_requested_at !== null
            && ! $this->hasCompleteContact($conversation);
    }

    public function hasCompleteContact(AssistantConversation $conversation): bool
    {
        $name = trim((string) ($conversation->visitor_name ?? ''));
        $email = trim((string) ($conversation->visitor_email ?? ''));
        $phone = $this->normalizePhone((string) ($conversation->visitor_phone ?? ''));

        return $name !== ''
            && $email !== ''
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && $phone !== '';
    }

    public function beginCallbackRequest(AssistantConversation $conversation, string $dealershipName): string
    {
        if (! $conversation->contact_callback_requested_at) {
            $conversation->update(['contact_callback_requested_at' => now()]);
            $conversation->refresh();
        }

        if ($this->hasCompleteContact($conversation)) {
            return $this->completionMessage($dealershipName);
        }

        return 'En este momento no hay asesores en línea en '.$dealershipName
            .', pero puedo seguir ayudándote aquí. Para que un asesor te contacte después, comparte tu '
            .$this->missingFieldsPrompt($conversation)
            .'. Puedes enviarlo en un solo mensaje (por ejemplo: Juan Pérez, juan@correo.com, 5512345678).';
    }

    /**
     * @return array{text: string}|null
     */
    public function tryHandleMessage(AssistantConversation $conversation, string $userText): ?array
    {
        if (! $conversation->contact_callback_requested_at) {
            return null;
        }

        if ($this->hasCompleteContact($conversation)) {
            return null;
        }

        $conversation->loadMissing('dealership');
        $dealershipName = $conversation->dealership?->name ?? 'Grupo VECSA';

        $this->extractAndSaveFromText($conversation, $userText);
        $conversation->refresh();

        if ($this->hasCompleteContact($conversation)) {
            return ['text' => $this->completionMessage($dealershipName)];
        }

        return [
            'text' => 'Gracias. Aún necesito tu '.$this->missingFieldsPrompt($conversation)
                .' para que un asesor de '.$dealershipName.' te contacte cuando esté disponible.',
        ];
    }

    private function completionMessage(string $dealershipName): string
    {
        return 'Perfecto, registré tus datos. Un asesor de '.$dealershipName
            .' te contactará pronto. Mientras tanto, puedes seguir preguntando lo que necesites por este chat.';
    }

    private function missingFieldsPrompt(AssistantConversation $conversation): string
    {
        $missing = [];
        if (trim((string) ($conversation->visitor_name ?? '')) === '') {
            $missing[] = 'nombre completo';
        }
        if (! $this->hasValidEmail($conversation)) {
            $missing[] = 'correo electrónico';
        }
        if ($this->normalizePhone((string) ($conversation->visitor_phone ?? '')) === '') {
            $missing[] = 'teléfono';
        }

        return $this->joinList($missing);
    }

    private function hasValidEmail(AssistantConversation $conversation): bool
    {
        $email = trim((string) ($conversation->visitor_email ?? ''));

        return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function extractAndSaveFromText(AssistantConversation $conversation, string $text): void
    {
        $updates = [];

        if (! $this->hasValidEmail($conversation)
            && preg_match('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i', $text, $emailMatch)) {
            $updates['visitor_email'] = mb_strtolower($emailMatch[0]);
        }

        if ($this->normalizePhone((string) ($conversation->visitor_phone ?? '')) === ''
            && preg_match('/(?:\+?52\s*)?(?:\d[\s\-]?){9,10}\d/', $text, $phoneMatch)) {
            $normalized = $this->normalizePhone($phoneMatch[0]);
            if ($normalized !== '') {
                $updates['visitor_phone'] = $normalized;
            }
        }

        if (trim((string) ($conversation->visitor_name ?? '')) === '') {
            $name = $this->extractNameFromText($text, $updates['visitor_email'] ?? null);
            if ($name !== '') {
                $updates['visitor_name'] = $name;
            }
        }

        if ($updates !== []) {
            $conversation->update($updates);
        }
    }

    private function extractNameFromText(string $text, ?string $emailToStrip): string
    {
        $working = trim($text);
        if ($emailToStrip) {
            $working = str_replace($emailToStrip, '', $working);
        }
        $working = preg_replace('/(?:\+?52\s*)?(?:\d[\s\-]?){9,10}\d/', '', $working) ?? $working;
        $working = preg_replace('/[^\p{L}\s]/u', ' ', $working) ?? $working;
        $working = preg_replace('/\s+/u', ' ', trim($working)) ?? '';

        if ($working === '' || mb_strlen($working) < 3) {
            return '';
        }

        $lower = mb_strtolower($working);
        if (preg_match('/^(s[ií]|sip|no|ok|vale|gracias|hola|buenas)\b/u', $lower)) {
            return '';
        }

        return mb_convert_case($working, MB_CASE_TITLE, 'UTF-8');
    }

    private function normalizePhone(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return '';
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '52')) {
            $digits = substr($digits, 2);
        }

        return strlen($digits) >= 10 ? $digits : '';
    }

    /**
     * @param  list<string>  $items
     */
    private function joinList(array $items): string
    {
        $count = count($items);
        if ($count === 0) {
            return '';
        }
        if ($count === 1) {
            return $items[0];
        }
        if ($count === 2) {
            return $items[0].' y '.$items[1];
        }

        $last = array_pop($items);

        return implode(', ', $items).', y '.$last;
    }
}
