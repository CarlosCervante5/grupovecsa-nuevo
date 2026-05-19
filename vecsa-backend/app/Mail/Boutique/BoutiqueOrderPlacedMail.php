<?php

namespace App\Mail\Boutique;

use App\Models\Boutique\BoutiqueOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BoutiqueOrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $transferBank
     */
    public function __construct(
        public BoutiqueOrder $order,
        public array $transferBank,
    ) {}

    public function envelope(): Envelope
    {
        $from = config('boutique.mail_from', []);

        return new Envelope(
            from: ! empty($from['address'])
                ? new \Illuminate\Mail\Mailables\Address($from['address'], $from['name'] ?? null)
                : null,
            subject: 'Pedido ' . $this->order->order_number . ' — Grupo VECSA Boutique',
        );
    }

    public function content(): Content
    {
        $payment = $this->order->payment;
        $method = $payment?->method ?? '';

        return new Content(
            view: 'emails.boutique.order_placed',
            with: [
                'order' => $this->order,
                'items' => $this->order->orderItems,
                'paymentMethod' => $method,
                'showTransferBank' => $method === 'transferencia',
                'transferBank' => $this->transferBank,
                'frontendUrl' => rtrim((string) config('app.frontend_url', config('app.url')), '/'),
            ],
        );
    }
}
