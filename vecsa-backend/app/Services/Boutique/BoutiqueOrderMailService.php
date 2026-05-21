<?php

namespace App\Services\Boutique;

use App\Jobs\SendBoutiqueOrderEmailJob;
use App\Models\Boutique\BoutiqueOrder;
use Illuminate\Support\Facades\Log;

/**
 * Correos transaccionales boutique vía mailer Resend (cola database).
 */
class BoutiqueOrderMailService
{
    public function sendOrderPlaced(BoutiqueOrder $order): void
    {
        $email = $this->recipientEmail($order);
        if ($email === null) {
            return;
        }

        try {
            SendBoutiqueOrderEmailJob::dispatch($order->uuid, 'placed', $email);
        } catch (\Throwable $e) {
            Log::error('Boutique: no se pudo encolar correo de pedido creado', [
                'order_uuid' => $order->uuid,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function sendOrderPaid(BoutiqueOrder $order): void
    {
        $email = $this->recipientEmail($order);
        if ($email === null) {
            return;
        }

        try {
            SendBoutiqueOrderEmailJob::dispatch($order->uuid, 'paid', $email);
        } catch (\Throwable $e) {
            Log::error('Boutique: no se pudo encolar correo de pago confirmado', [
                'order_uuid' => $order->uuid,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function recipientEmail(BoutiqueOrder $order): ?string
    {
        $order->loadMissing(['user', 'payment']);

        $email = trim((string) ($order->guest_email ?? ''));
        if ($email === '' && $order->user_id) {
            $email = trim((string) ($order->user?->email ?? ''));
        }

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Boutique email: sin destinatario válido', ['order_uuid' => $order->uuid]);

            return null;
        }

        return $email;
    }
}
