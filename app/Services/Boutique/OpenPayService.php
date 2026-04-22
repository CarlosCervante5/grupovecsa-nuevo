<?php

namespace App\Services\Boutique;

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
            $raw = $response->body();
            $body = $response->json();
            $msg = is_array($body)
                ? (string) ($body['description'] ?? $body['error_description'] ?? $raw)
                : $raw;
            if ($msg === '' || $msg === '0') {
                $msg = 'Error al procesar el cargo en OpenPay (HTTP ' . $response->status() . ').';
            }
            Log::warning('OpenPay charge error', [
                'url' => $url,
                'http_status' => $response->status(),
                'body' => is_array($body) ? $body : $raw,
            ]);
            throw new Exception($msg);
        }

        $decoded = $response->json();
        if (! is_array($decoded)) {
            $snippet = mb_substr($response->body() ?: '', 0, 500);
            throw new Exception(
                'Respuesta inválida de OpenPay (esperado JSON con el cargo; fragmento: ' . $snippet . ').'
            );
        }

        return $decoded;
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
}
