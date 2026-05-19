<?php

namespace App\Mail\Boutique;

use App\Models\Boutique\BoutiqueOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BoutiqueOrderPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BoutiqueOrder $order,
    ) {}

    public function envelope(): Envelope
    {
        $from = config('boutique.mail_from', []);

        return new Envelope(
            from: ! empty($from['address'])
                ? new \Illuminate\Mail\Mailables\Address($from['address'], $from['name'] ?? null)
                : null,
            subject: 'Pago confirmado — pedido ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.boutique.order_paid',
            with: [
                'order' => $this->order,
                'items' => $this->order->orderItems,
                'frontendUrl' => rtrim((string) config('app.frontend_url', config('app.url')), '/'),
            ],
        );
    }
}
