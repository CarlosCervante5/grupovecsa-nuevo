<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InternallyRegisterNotification extends Notification
{
    use Queueable;

    protected $password;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($password)
    {
        $this->password = $password;
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
        $url = config('app.frontend_url') . '/auth/recuperar';

        return (new MailMessage)
            ->subject('¡Registro exitoso en Grupovecsa!')
            ->line('Estás recibiendo este correo electrónico porque recibimos una solicitud de registro con este correo.')
            ->line('Esta es tu contraseña temporal.')
            ->line($this->password)
            ->line('¡No olvides cambiar tu contraseña por seguridad de tus datos!')
            ->action('¡Sigue el enlace para cambiar tu contraseña mediante nuestro proceso de recuperar cuenta!', $url);
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
