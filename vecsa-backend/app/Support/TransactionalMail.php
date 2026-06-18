<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

/**
 * Correos transaccionales (registro, recuperar cuenta, pedidos boutique, formularios).
 * Usa el mailer Resend configurado en MAIL_TRANSACTIONAL_MAILER.
 */
final class TransactionalMail
{
    public static function mailer(): string
    {
        return (string) config('mail.transactional_mailer', 'resend');
    }

    /**
     * @return array{address: string, name: string|null}
     */
    public static function from(): array
    {
        $from = config('mail.transactional_from', []);

        return [
            'address' => (string) ($from['address'] ?? config('mail.from.address', '')),
            'name' => isset($from['name']) && $from['name'] !== '' ? (string) $from['name'] : null,
        ];
    }

    public static function send(Mailable $mailable, string|array|null $recipients): void
    {
        $to = array_values(array_filter(
            is_array($recipients) ? $recipients : [$recipients],
            static fn ($email) => is_string($email) && trim($email) !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)
        ));

        if ($to === []) {
            return;
        }

        Mail::mailer(self::mailer())->to($to)->send($mailable);
    }
}
