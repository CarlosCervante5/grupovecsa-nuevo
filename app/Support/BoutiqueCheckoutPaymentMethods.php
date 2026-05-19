<?php

namespace App\Support;

use App\Models\SystemSetting;
use App\Services\Boutique\OpenPayService;

/**
 * Métodos de pago disponibles en el checkout boutique (tarjeta vía OpenPay;
 * transferencia y sucursal por flags en SystemSetting).
 */
final class BoutiqueCheckoutPaymentMethods
{
    public static function openpayKeysConfigured(): bool
    {
        $openpayMode = SystemSetting::get('openpay_mode', 'sandbox');
        $suffixOp = $openpayMode === 'production' ? 'production' : 'sandbox';
        $merchantId = trim((string) SystemSetting::get("openpay_{$suffixOp}_merchant_id", ''));
        $publicKey = trim((string) SystemSetting::get("openpay_{$suffixOp}_public_key", ''));

        return $merchantId !== '' && $publicKey !== '';
    }

    /**
     * @return array{methods: array{stripe: bool, openpay: bool, transferencia: bool, sucursal: bool}, openpay: array{merchant_id: string, public_key: string, sandbox: bool, available: bool}}
     */
    public static function publicPayload(): array
    {
        $openpayMode = SystemSetting::get('openpay_mode', 'sandbox');
        $suffixOp = $openpayMode === 'production' ? 'production' : 'sandbox';
        $merchantId = trim((string) SystemSetting::get("openpay_{$suffixOp}_merchant_id", ''));
        $publicKey = trim((string) SystemSetting::get("openpay_{$suffixOp}_public_key", ''));
        $openpayFlag = filter_var(SystemSetting::get('boutique_checkout_openpay', '1'), FILTER_VALIDATE_BOOLEAN);
        $openpayOn = self::openpayKeysConfigured() && $openpayFlag;

        $transferencia = filter_var(SystemSetting::get('boutique_checkout_transferencia', '1'), FILTER_VALIDATE_BOOLEAN);
        $sucursal = filter_var(SystemSetting::get('boutique_checkout_sucursal', '1'), FILTER_VALIDATE_BOOLEAN);

        return [
            'methods' => [
                'stripe' => false,
                'openpay' => $openpayOn,
                'transferencia' => $transferencia,
                'sucursal' => $sucursal,
            ],
            'transfer_bank' => BoutiqueTransferBankDetails::publicPayload(),
            'openpay' => [
                'merchant_id' => $merchantId,
                'public_key' => $publicKey,
                'sandbox' => $openpayMode !== 'production',
                'available' => $openpayOn,
            ],
        ];
    }

    /**
     * Configuración para el panel tienda (incluye flags editables y URL de webhook).
     *
     * @return array<string, mixed>
     */
    public static function adminConfigPayload(): array
    {
        $public = self::publicPayload();
        $openPayService = app(OpenPayService::class);

        return array_merge($public, [
            'transfer_bank' => BoutiqueTransferBankDetails::publicPayload(),
            'admin' => [
                'keys_configured' => [
                    'stripe' => false,
                    'openpay' => self::openpayKeysConfigured(),
                ],
                'flags' => [
                    'boutique_checkout_stripe' => false,
                    'boutique_checkout_openpay' => filter_var(SystemSetting::get('boutique_checkout_openpay', '1'), FILTER_VALIDATE_BOOLEAN),
                    'boutique_checkout_transferencia' => filter_var(SystemSetting::get('boutique_checkout_transferencia', '1'), FILTER_VALIDATE_BOOLEAN),
                    'boutique_checkout_sucursal' => filter_var(SystemSetting::get('boutique_checkout_sucursal', '1'), FILTER_VALIDATE_BOOLEAN),
                ],
                'webhook_url' => $openPayService->webhookUrl(),
            ],
        ]);
    }

    public static function isMethodEnabled(string $method): bool
    {
        if ($method === 'stripe') {
            return false;
        }
        $allowed = ['openpay', 'transferencia', 'sucursal'];
        if (! in_array($method, $allowed, true)) {
            return false;
        }
        $payload = self::publicPayload();

        return ($payload['methods'][$method] ?? false) === true;
    }

    public static function hasAnyEnabledMethod(): bool
    {
        $m = self::publicPayload()['methods'];

        return ($m['openpay'] || $m['transferencia'] || $m['sucursal']) === true;
    }
}
