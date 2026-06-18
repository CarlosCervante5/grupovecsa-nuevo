<?php

namespace App\Notifications;

use App\Notifications\Concerns\UsesTransactionalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RegisterNotification extends Notification
{
    use Queueable;
    use UsesTransactionalMail;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        return $this->transactionalMailMessage()
            ->subject('¡Registro exitoso en Grupo VECSA!')
            ->greeting('¡Bienvenido a Grupo VECSA!')
            ->line('Tu cuenta se creó correctamente con este correo.')
            ->line('Ya puedes iniciar sesión y completar tu perfil de rider.')
            ->action('Ir a mi cuenta', $frontendUrl.'/auth/mi-cuenta')
            ->line('Si no solicitaste este registro, ignora este mensaje.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
