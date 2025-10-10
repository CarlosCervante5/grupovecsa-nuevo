<?php

namespace App\Mail;
 
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReceptionNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Crea una nueva instancia del mensaje.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo cliente en recepción.',
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
            view: 'emails.reception_notification'
        );
    }
}
