<?php

namespace App\Support;

use App\Models\SystemSetting;

/**
 * Métodos de pago disponibles en el checkout boutique (tarjeta vía OpenPay;
 * transferencia y sucursal por flags en SystemSetting).
 */
final class BoutiqueCheckoutPaymentMethods
{
    /**
     * @return array{methods: array{stripe: bool, openpay: bool, transferencia: bool, sucursal: bool}, openpay: array{merchant_id: string, public_key: string, sandbox: bool, available: bool}}
     */
    public static function publicPayload(): array
    {
        $openpayMode = SystemSetting::get('openpay_mode', 'sandbox');
        $suffixOp = $openpayMode === 'production' ? 'production' : 'sandbox';
        $merchantId = trim((string) SystemSetting::get("openpay_{$suffixOp}_merchant_id", ''));
        $publicKey = trim((string) SystemSetting::get("openpay_{$suffixOp}_public_key", ''));
        $openpayOn = $merchantId !== '' && $publicKey !== '';

        $transferencia = filter_var(SystemSetting::get('boutique_checkout_transferencia', '1'), FILTER_VALIDATE_BOOLEAN);
        $sucursal = filter_var(SystemSetting::get('boutique_checkout_sucursal', '1'), FILTER_VALIDATE_BOOLEAN);

        return [
            'methods' => [
                'stripe' => false,
                'openpay' => $openpayOn,
                'transferencia' => $transferencia,
                'sucursal' => $sucursal,
            ],
            'openpay' => [
                'merchant_id' => $merchantId,
                'public_key' => $publicKey,
                'sandbox' => $openpayMode !== 'production',
                'available' => $openpayOn,
            ],
        ];
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
