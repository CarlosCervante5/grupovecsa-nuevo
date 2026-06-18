<?php

namespace App\Notifications\Concerns;

use App\Support\TransactionalMail;
use Illuminate\Notifications\Messages\MailMessage;

trait UsesTransactionalMail
{
    protected function transactionalMailMessage(): MailMessage
    {
        $from = TransactionalMail::from();

        $message = (new MailMessage)->mailer(TransactionalMail::mailer());

        if ($from['address'] !== '') {
            $message->from($from['address'], $from['name']);
        }

        return $message;
    }
}
