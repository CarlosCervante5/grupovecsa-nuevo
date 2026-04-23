<?php

namespace App\Services\Boutique;

use App\Models\Boutique\BoutiqueOrder;
use App\Models\Boutique\BoutiquePayment;
use App\Models\SystemSetting;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripeService
{
    protected string $secretKey;
    protected string $webhookSecret;

    public function __construct(
        protected BoutiqueInventoryService $inventoryService
    ) {
        $mode = SystemSetting::get('stripe_mode', 'test');
        $this->secretKey = SystemSetting::getEncrypted("stripe_{$mode}_secret_key")
            ?? env('STRIPE_SECRET_KEY', '');
        $this->webhookSecret = SystemSetting::getEncrypted("stripe_{$mode}_webhook_secret")
            ?? env('STRIPE_WEBHOOK_SECRET', '');
    }

    /**
     * Create a Stripe PaymentIntent and return the client_secret.
     */
    public function createPaymentIntent(float $amount, string $currency = 'mxn', array $metadata = []): array
    {
        \Stripe\Stripe::setApiKey($this->secretKey);

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => (int) round($amount * 100), // Stripe uses cents
            'currency' => $currency,
            'metadata' => $metadata,
        ]);

        return [
            'client_secret' => $paymentIntent->client_secret,
            'payment_intent_id' => $paymentIntent->id,
        ];
    }

    /**
     * Verify Stripe webhook signature.
     *
     * @throws Exception if signature is invalid
     */
    public function verifyWebhookSignature(string $payload, string $sigHeader): array
    {
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $this->webhookSecret
            );

            return json_decode(json_encode($event), true);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            throw new Exception('Firma de webhook inválida: ' . $e->getMessage());
        }
    }

    /**
     * Process a payment_intent.succeeded event.
     */
    public function processPaymentSucceeded(array $event): void
    {
        $paymentIntentId = $event['data']['object']['id'] ?? null;

        if (! $paymentIntentId) {
            Log::error('Stripe webhook: payment_intent_id not found in event');
            return;
        }

        DB::transaction(function () use ($paymentIntentId) {
            $payment = BoutiquePayment::query()
                ->lockForUpdate()
                ->where('stripe_payment_intent_id', $paymentIntentId)
                ->first();

            if (! $payment) {
                Log::error('Stripe webhook: Payment not found for intent: ' . $paymentIntentId);
                return;
            }

            if ($payment->status === 'completado') {
                return;
            }

            if ($payment->method !== 'stripe') {
                return;
            }

            $order = BoutiqueOrder::query()
                ->lockForUpdate()
                ->find($payment->order_id);

            if (! $order) {
                Log::error('Stripe webhook: order not found for payment id ' . (string) $payment->id);
                return;
            }

            if ($this->inventoryService->orderLacksStockForItems($order)) {
                Log::critical('Stripe: pago capturado pero stock insuficiente; requiere revisión o reembolso', [
                    'payment_intent_id' => $paymentIntentId,
                    'order_uuid' => $order->uuid,
                ]);
                return;
            }

            $this->inventoryService->applySaleForEntireOrder($order, (string) $order->uuid, 'venta');

            $payment->update([
                'status' => 'completado',
                'confirmed_at' => now(),
            ]);

            $order->update(['status' => 'pagado']);
        });

        $paymentForLog = BoutiquePayment::query()
            ->where('stripe_payment_intent_id', $paymentIntentId)
            ->first();
        $order = $paymentForLog?->order;
        if ($order && $paymentForLog?->status === 'completado') {
            Log::info('Stripe payment processed successfully', [
                'payment_intent_id' => $paymentIntentId,
                'order_uuid' => $order->uuid,
            ]);
        }
    }
}
