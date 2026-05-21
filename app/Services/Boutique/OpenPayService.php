<?php

namespace App\Services\Boutique;

use App\Models\Boutique\BoutiqueOrder;
use App\Models\Boutique\BoutiquePayment;
use App\Models\SystemSetting;
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
     * @throws OpenPayChargeException
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
            throw new OpenPayChargeException('OpenPay no está configurado (merchant o llave privada vacíos).', 503);
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
            'origin_channel' => 'PLUGIN_WOOCOMMERCE',
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
            $code = is_array($body) ? ($body['error_code'] ?? $body['category'] ?? null) : null;
            Log::warning('OpenPay charge error', [
                'status' => $response->status(),
                'body' => $body,
            ]);
            $userMsg = is_string($msg) ? $msg : 'Error al procesar el cargo en OpenPay.';
            if (in_array($response->status(), [401, 403], true)) {
                $userMsg .= ' Revise en administración que la llave privada (sk_…) sea del mismo comercio y modo (sandbox/producción) que la llave pública del checkout.';
            }

            throw new OpenPayChargeException(
                $userMsg,
                $response->status(),
                is_array($body) ? $body : null,
                is_string($code) ? $code : null,
            );
        }

        return $response->json();
    }

    /**
     * Datos de cliente exigidos por OpenPay MX (incluye domicilio).
     *
     * @return array<string, mixed>
     */
    public function buildCustomerFromOrder(BoutiqueOrder $order): array
    {
        $fullName = trim((string) ($order->shipping_name ?: $order->guest_name ?: 'Cliente'));
        $nameParts = preg_split('/\s+/', $fullName, 2) ?: ['Cliente', ''];
        $firstName = $nameParts[0] !== '' ? $nameParts[0] : 'Cliente';
        $lastName = isset($nameParts[1]) && $nameParts[1] !== '' ? $nameParts[1] : $firstName;

        $email = trim((string) ($order->guest_email ?: ''));
        if ($email === '' && $order->user_id) {
            $order->loadMissing('user');
            $email = trim((string) ($order->user?->email ?? ''));
        }
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new OpenPayChargeException('Correo del pedido inválido para procesar el pago.', 422);
        }

        $phone = preg_replace('/\D+/', '', (string) ($order->shipping_phone ?? ''));
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }
        if (strlen($phone) < 10) {
            $phone = '5555555555';
        }

        $zip = preg_replace('/\D/', '', (string) ($order->shipping_zip ?? ''));
        if (strlen($zip) > 5) {
            $zip = substr($zip, 0, 5);
        }
        if ($zip === '') {
            $zip = '00000';
        }

        $line1 = trim((string) ($order->shipping_address ?? ''));
        if ($line1 === '') {
            $line1 = 'Domicilio';
        }

        return [
            'name' => mb_substr($firstName, 0, 100),
            'last_name' => mb_substr($lastName, 0, 100),
            'phone_number' => $phone,
            'email' => mb_substr($email, 0, 100),
            'requires_account' => false,
            'address' => [
                'line1' => mb_substr($line1, 0, 200),
                'line2' => '',
                'city' => mb_substr(trim((string) ($order->shipping_city ?? 'Ciudad')), 0, 100),
                'state' => mb_substr(trim((string) ($order->shipping_state ?? 'Estado')), 0, 100),
                'postal_code' => $zip,
                'country_code' => 'MX',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $charge
     */
    public function chargeRequires3ds(array $charge): bool
    {
        $status = $charge['status'] ?? '';
        $pm = $charge['payment_method'] ?? null;

        return $status === 'in_progress'
            && is_array($pm)
            && ($pm['type'] ?? '') === 'redirect'
            && ! empty($pm['url']);
    }

    /**
     * @param  array<string, mixed>  $charge
     */
    public function charge3dsRedirectUrl(array $charge): ?string
    {
        if (! $this->chargeRequires3ds($charge)) {
            return null;
        }

        $url = $charge['payment_method']['url'] ?? null;

        return is_string($url) && $url !== '' ? $url : null;
    }

    /**
     * @return array{merchant_id: string, private_key: string, sandbox: bool}
     */
    public function getActiveCredentials(): array
    {
        $mode = SystemSetting::get('openpay_mode', 'sandbox');
        $suffix = $mode === 'production' ? 'production' : 'sandbox';

        $merchantId = trim((string) SystemSetting::get("openpay_{$suffix}_merchant_id", ''));

        return [
            'merchant_id' => $merchantId,
            'private_key' => $this->resolvePrivateKey($suffix),
            'sandbox' => $mode !== 'production',
        ];
    }

    /**
     * Llave privada activa: cifrada en BD, texto plano legacy o variable de entorno.
     */
    protected function resolvePrivateKey(string $suffix): string
    {
        $encrypted = SystemSetting::getEncrypted("openpay_{$suffix}_private_key");
        if (is_string($encrypted) && trim($encrypted) !== '') {
            return trim($encrypted);
        }

        $plain = trim((string) SystemSetting::get("openpay_{$suffix}_private_key", ''));
        if ($plain !== '' && str_starts_with($plain, 'sk_')) {
            return $plain;
        }

        return trim((string) env('OPENPAY_PRIVATE_KEY', ''));
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
