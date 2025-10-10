<?php

namespace App\Mail;
 
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecoverAccount extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $newPassword;

    /**
     * Crea una nueva instancia del mensaje.
     *
     * @return void
     */
    public function __construct($user, $newPassword)
    {
        $this->name = $user;
        $this->newPassword = $newPassword;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Contraseña.',
        );
    }

    /**
     * Construye el mensaje.
     *
     * @return $this
     */
    public function content(): Content
    {   
        return new Content(
            view: 'emails.recover_account',
            with: [
                'app' => env('APP_NAME',''),
                'name' => $this->name,
                'newPassword' => $this->newPassword,
            ],
        );
    }
}
