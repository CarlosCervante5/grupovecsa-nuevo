<?php

namespace App\Support;

use App\Models\SystemSetting;

/**
 * Métodos de pago del checkout boutique: llaves (Stripe/OpenPay) + flags en
 * `boutique_checkout_*` en SystemSetting (cada uno puede apagarse en admin).
 */
final class BoutiqueCheckoutPaymentMethods
{
    /**
     * @return array{stripe: bool, openpay: bool}
     */
    public static function keysPresent(): array
    {
        $stripeMode = SystemSetting::get('stripe_mode', 'test');
        if (! in_array($stripeMode, ['test', 'live'], true)) {
            $stripeMode = 'test';
        }
        $pkStripe = trim((string) SystemSetting::get("stripe_{$stripeMode}_publishable_key", ''));
        $openpayMode = SystemSetting::get('openpay_mode', 'sandbox');
        $suffixOp = $openpayMode === 'production' ? 'production' : 'sandbox';
        $merchantId = trim((string) SystemSetting::get("openpay_{$suffixOp}_merchant_id", ''));
        $publicKey = trim((string) SystemSetting::get("openpay_{$suffixOp}_public_key", ''));

        return [
            'stripe' => $pkStripe !== '',
            'openpay' => $merchantId !== '' && $publicKey !== '',
        ];
    }

    /**
     * Comprueba que, con las banderas dadas, quede al menos un método visible
     * (p. ej. al guardar en el admin, antes de persistir).
     */
    public static function hasAnyWithFlags(
        bool $flagStripe,
        bool $flagOpenpay,
        bool $flagTransferencia,
        bool $flagSucursal
    ): bool {
        $keys = self::keysPresent();
        if ($keys['stripe'] && $flagStripe) {
            return true;
        }
        if ($keys['openpay'] && $flagOpenpay) {
            return true;
        }

        return $flagTransferencia || $flagSucursal;
    }

    /**
     * @return array{methods: array{stripe: bool, openpay: bool, transferencia: bool, sucursal: bool}, openpay: array{merchant_id: string, public_key: string, sandbox: bool, available: bool}, admin?: array{keys_configured: array{stripe: bool, openpay: bool}, flags: array{boutique_checkout_stripe: bool, boutique_checkout_openpay: bool, boutique_checkout_transferencia: bool, boutique_checkout_sucursal: bool}}}
     */
    public static function publicPayload(bool $includeAdmin = false): array
    {
        $stripeMode = SystemSetting::get('stripe_mode', 'test');
        if (! in_array($stripeMode, ['test', 'live'], true)) {
            $stripeMode = 'test';
        }
        $pkStripe = trim((string) SystemSetting::get("stripe_{$stripeMode}_publishable_key", ''));
        $stripeHasKeys = $pkStripe !== '';

        $openpayMode = SystemSetting::get('openpay_mode', 'sandbox');
        $suffixOp = $openpayMode === 'production' ? 'production' : 'sandbox';
        $merchantId = trim((string) SystemSetting::get("openpay_{$suffixOp}_merchant_id", ''));
        $publicKey = trim((string) SystemSetting::get("openpay_{$suffixOp}_public_key", ''));
        $openpayHasKeys = $merchantId !== '' && $publicKey !== '';

        $flagStripe = filter_var(SystemSetting::get('boutique_checkout_stripe', '1'), FILTER_VALIDATE_BOOLEAN);
        $flagOpenpay = filter_var(SystemSetting::get('boutique_checkout_openpay', '1'), FILTER_VALIDATE_BOOLEAN);
        $flagTransferencia = filter_var(SystemSetting::get('boutique_checkout_transferencia', '1'), FILTER_VALIDATE_BOOLEAN);
        $flagSucursal = filter_var(SystemSetting::get('boutique_checkout_sucursal', '1'), FILTER_VALIDATE_BOOLEAN);

        $stripeOn = $stripeHasKeys && $flagStripe;
        $openpayOn = $openpayHasKeys && $flagOpenpay;
        $transferencia = $flagTransferencia;
        $sucursal = $flagSucursal;

        $base = [
            'methods' => [
                'stripe' => $stripeOn,
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

        if ($includeAdmin) {
            $base['admin'] = [
                'keys_configured' => [
                    'stripe' => $stripeHasKeys,
                    'openpay' => $openpayHasKeys,
                ],
                'flags' => [
                    'boutique_checkout_stripe' => $flagStripe,
                    'boutique_checkout_openpay' => $flagOpenpay,
                    'boutique_checkout_transferencia' => $flagTransferencia,
                    'boutique_checkout_sucursal' => $flagSucursal,
                ],
            ];
        }

        return $base;
    }

    public static function isMethodEnabled(string $method): bool
    {
        $allowed = ['stripe', 'openpay', 'transferencia', 'sucursal'];
        if (! in_array($method, $allowed, true)) {
            return false;
        }
        $payload = self::publicPayload();

        return ($payload['methods'][$method] ?? false) === true;
    }

    public static function hasAnyEnabledMethod(): bool
    {
        $m = self::publicPayload()['methods'];

        return ($m['stripe'] || $m['openpay'] || $m['transferencia'] || $m['sucursal']) === true;
    }
}
