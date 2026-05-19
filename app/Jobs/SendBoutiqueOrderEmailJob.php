<?php

namespace App\Jobs;

use App\Mail\Boutique\BoutiqueOrderPaidMail;
use App\Mail\Boutique\BoutiqueOrderPlacedMail;
use App\Models\Boutique\BoutiqueOrder;
use App\Support\BoutiqueTransferBankDetails;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBoutiqueOrderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public string $orderUuid,
        public string $type,
        public string $recipientEmail,
    ) {}

    public function handle(): void
    {
        $order = BoutiqueOrder::where('uuid', $this->orderUuid)
            ->with(['orderItems', 'payment', 'shipment'])
            ->first();

        if (! $order) {
            Log::warning('SendBoutiqueOrderEmailJob: pedido no encontrado', ['uuid' => $this->orderUuid]);

            return;
        }

        $mailer = config('boutique.mail_mailer', 'resend');

        $mailable = match ($this->type) {
            'placed' => new BoutiqueOrderPlacedMail(
                $order,
                BoutiqueTransferBankDetails::publicPayload(),
            ),
            'paid' => new BoutiqueOrderPaidMail($order),
            default => null,
        };

        if ($mailable === null) {
            return;
        }

        Mail::mailer($mailer)->to($this->recipientEmail)->send($mailable);
    }
}
