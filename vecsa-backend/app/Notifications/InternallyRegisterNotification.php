<?php

namespace App\Notifications;

use App\Notifications\Concerns\UsesTransactionalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InternallyRegisterNotification extends Notification
{
    use Queueable;
    use UsesTransactionalMail;

    protected $password;

    public function __construct($password)
    {
        $this->password = $password;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $url = rtrim((string) config('app.frontend_url', config('app.url')), '/').'/auth/recuperar';

        return $this->transactionalMailMessage()
            ->subject('¡Registro exitoso en Grupo VECSA!')
            ->line('Se creó tu cuenta con este correo.')
            ->line('Contraseña temporal: '.$this->password)
            ->line('Por seguridad, cámbiala lo antes posible.')
            ->action('Cambiar contraseña', $url);
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
