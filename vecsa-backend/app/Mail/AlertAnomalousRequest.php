<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlertAnomalousRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $ip;
    public $path;
    public $method;

    /**
     * Create a new message instance.
     */
    public function __construct($ip, $path, $method)
    {
        $this->ip = $ip;
        $this->path = $path;
        $this->method = $method;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Alerta de solicitudes al servidor.',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.anomalous_request',
            with: [
                'ip' => $this->ip,
                'path' => $this->path,
                'method' => $this->method,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
