<?php

namespace App\Services\Boutique;

use App\Models\Boutique\BoutiqueOrder;
use App\Models\Boutique\BoutiquePayment;
use App\Models\SystemSetting;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenPayService
{
    protected function baseUrl(bool $sandbox): string
    {
        return $sandbox
            ? 'https://sandbox-api.openpay.mx'
            : 'https://api.openpay.mx';
    }

    /**
     * Crea un cargo con tarjeta (token) a nivel comercio.
     *
     * @see https://documents.openpay.mx/docs/api/
     *
     * @return array<string, mixed> Respuesta JSON del cargo
     *
     * @throws Exception
     */
    public function createMerchantCardCharge(
        string $merchantId,
        string $privateKey,
        bool $sandbox,
        string $sourceId,
        string $deviceSessionId,
        float $amount,
        string $description,
        string $orderId,
        array $customer,
        ?string $clientIp = null,
        ?string $userAgent = null
    ): array {
        $merchantId = trim($merchantId);
        $privateKey = trim($privateKey);
        if ($merchantId === '' || $privateKey === '') {
            throw new Exception('OpenPay no está configurado (merchant o llave privada vacíos).');
        }

        $url = $this->baseUrl($sandbox) . '/v1/' . rawurlencode($merchantId) . '/charges';

        $payload = [
            'source_id' => $sourceId,
            'method' => 'card',
            'amount' => round($amount, 2),
            'currency' => 'MXN',
            'description' => mb_substr($description, 0, 250),
            'order_id' => mb_substr($orderId, 0, 100),
            'device_session_id' => $deviceSessionId,
            'customer' => $customer,
        ];

        if ($clientIp !== null || $userAgent !== null) {
            $payload['http_context'] = array_filter([
                'ip' => $clientIp,
                'browser_user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 512) : null,
            ], fn ($v) => $v !== null && $v !== '');
        }

        /** @var Response $response */
        $response = Http::withBasicAuth($privateKey, '')
            ->acceptJson()
            ->asJson()
            ->post($url, $payload);

        if (! $response->successful()) {
            $body = $response->json();
            $msg = is_array($body)
                ? ($body['description'] ?? $body['error_description'] ?? $response->body())
                : $response->body();
            Log::warning('OpenPay charge error', [
                'status' => $response->status(),
                'body' => $body,
            ]);
            throw new Exception(is_string($msg) ? $msg : 'Error al procesar el cargo en OpenPay.');
        }

        return $response->json();
    }

    /**
     * @return array{merchant_id: string, private_key: string, sandbox: bool}
     */
    public function getActiveCredentials(): array
    {
        $mode = SystemSetting::get('openpay_mode', 'sandbox');
        $suffix = $mode === 'production' ? 'production' : 'sandbox';

        $merchantId = trim((string) SystemSetting::get("openpay_{$suffix}_merchant_id", ''));
        $privateKey = SystemSetting::getEncrypted("openpay_{$suffix}_private_key")
            ?? trim((string) env('OPENPAY_PRIVATE_KEY', ''));

        return [
            'merchant_id' => $merchantId,
            'private_key' => trim((string) $privateKey),
            'sandbox' => $mode !== 'production',
        ];
    }

    /**
     * @param  array<string, mixed>  $charge
     */
    public function chargeIsSuccessful(array $charge): bool
    {
        $status = $charge['status'] ?? '';

        return $status === 'completed';
    }

    public function webhookUrl(): string
    {
        return rtrim((string) config('app.url'), '/') . '/api/boutique/webhook/openpay';
    }

    /**
     * Basic auth configurado al registrar el webhook en OpenPay.
     */
    public function verifyWebhookAuth(?string $user, ?string $password): bool
    {
        $expectedUser = trim((string) SystemSetting::get('openpay_webhook_user', ''));
        $expectedPass = trim((string) (SystemSetting::getEncrypted('openpay_webhook_password') ?? ''));

        if ($expectedUser === '' || $expectedPass === '') {
            return false;
        }

        return hash_equals($expectedUser, (string) $user)
            && hash_equals($expectedPass, (string) $password);
    }

    /**
     * Procesa notificación webhook (verification, charge.succeeded, charge.failed, etc.).
     *
     * @param  array<string, mixed>  $payload
     */
    public function processWebhook(array $payload): void
    {
        $type = (string) ($payload['type'] ?? '');

        if ($type === 'verification') {
            Log::info('OpenPay webhook verification received', [
                'verification_code' => $payload['verification_code'] ?? null,
            ]);

            return;
        }

        $transaction = $payload['transaction'] ?? null;
        if (! is_array($transaction)) {
            Log::warning('OpenPay webhook sin transaction', ['type' => $type]);

            return;
        }

        $payment = $this->resolvePaymentFromTransaction($transaction);
        if (! $payment) {
            Log::warning('OpenPay webhook: pago no encontrado', [
                'type' => $type,
                'charge_id' => $transaction['id'] ?? null,
                'order_id' => $transaction['order_id'] ?? null,
            ]);

            return;
        }

        if ($payment->method !== 'openpay') {
            Log::warning('OpenPay webhook: método de pago distinto', [
                'payment_uuid' => $payment->uuid,
                'method' => $payment->method,
            ]);

            return;
        }

        $chargeStatus = (string) ($transaction['status'] ?? '');
        $chargeId = $transaction['id'] ?? null;

        if ($type === 'charge.succeeded' || $chargeStatus === 'completed') {
            $this->markPaymentCompleted($payment, is_string($chargeId) ? $chargeId : null);

            return;
        }

        if (in_array($type, ['charge.failed', 'charge.cancelled'], true)
            || in_array($chargeStatus, ['failed', 'cancelled'], true)) {
            if ($payment->status !== 'completado') {
                $payment->update(['status' => 'fallido']);
            }

            return;
        }

        Log::info('OpenPay webhook ignorado', ['type' => $type, 'charge_status' => $chargeStatus]);
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    protected function resolvePaymentFromTransaction(array $transaction): ?BoutiquePayment
    {
        $chargeId = $transaction['id'] ?? null;
        if (is_string($chargeId) && $chargeId !== '') {
            $byCharge = BoutiquePayment::where('transaction_reference', $chargeId)->first();
            if ($byCharge) {
                return $byCharge;
            }
        }

        $orderId = $transaction['order_id'] ?? null;
        if (! is_string($orderId) || $orderId === '') {
            return null;
        }

        $order = BoutiqueOrder::where('order_number', $orderId)->first();

        return $order?->payment;
    }

    protected function markPaymentCompleted(BoutiquePayment $payment, ?string $chargeId): void
    {
        if ($payment->status === 'completado') {
            return;
        }

        $updates = [
            'status' => 'completado',
            'confirmed_at' => now(),
        ];
        if ($chargeId !== null && $chargeId !== '') {
            $updates['transaction_reference'] = $chargeId;
        }
        $payment->update($updates);

        $order = $payment->order;
        if ($order && $order->status === 'pendiente') {
            $order->update(['status' => 'pagado']);
        }

        if ($order) {
            app(BoutiqueOrderMailService::class)->sendOrderPaid($order->fresh());
        }

        Log::info('OpenPay webhook: pago completado', [
            'payment_uuid' => $payment->uuid,
            'order_number' => $order?->order_number,
            'charge_id' => $chargeId,
        ]);
    }
}
