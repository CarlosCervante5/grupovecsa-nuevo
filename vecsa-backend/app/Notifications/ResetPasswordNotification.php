<?php

namespace App\Notifications;

use App\Notifications\Concerns\UsesTransactionalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;
    use UsesTransactionalMail;

    protected $token_user;
    protected $token_validate;

    public function __construct($token_user, $token_validate)
    {
        $this->token_user = $token_user;
        $this->token_validate = $token_validate;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $url = rtrim((string) config('app.frontend_url', config('app.url')), '/')
            .'/auth/restablecer/'.urlencode($this->token_user).'/'.$this->token_validate;

        return $this->transactionalMailMessage()
            ->subject('Restablecimiento de contraseña — Grupo VECSA')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->action('Restablecer contraseña', $url)
            ->line('Si no solicitaste este cambio, ignora este correo.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
