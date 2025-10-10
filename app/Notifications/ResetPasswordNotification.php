<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    protected $token_user;
    protected $token_validate;

    /**
     * Create a notification instance.
     *
     * @param  string  $token_user
     * @param  string  $token_validate
     * @return void
     */
    public function __construct($token_user, $token_validate)
    {
        $this->token_user = $token_user;
        $this->token_validate = $token_validate;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = config('app.frontend_url') . '/auth/restablecer/' . urlencode($this->token_user) . '/' . $this->token_validate;

        return (new MailMessage)
            ->subject('Restablecimiento de Contraseña')
            ->line('Estás recibiendo este correo electrónico porque recibimos una solicitud de restablecimiento de contraseña para la cuenta creada con este correo.')
            ->line('Sigue el enlace para reestablecer tu acceso.')
            ->action('Restablecer Contraseña', $url)
            ->line('Si no solicitaste un restablecimiento de contraseña, ignora este correo.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
