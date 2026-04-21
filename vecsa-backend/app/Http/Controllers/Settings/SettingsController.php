<?php

namespace App\Http\Controllers\Settings;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private const SECRET_KEYS = [
        'stripe_test_secret_key',
        'stripe_test_webhook_secret',
        'stripe_live_secret_key',
        'stripe_live_webhook_secret',
    ];

    private const PUBLISHABLE_KEYS = [
        'stripe_test_publishable_key',
        'stripe_live_publishable_key',
    ];

    private const PREFIX_RULES = [
        'stripe_test_publishable_key' => 'pk_test_',
        'stripe_test_secret_key' => 'sk_test_',
        'stripe_test_webhook_secret' => 'whsec_',
        'stripe_live_publishable_key' => 'pk_live_',
        'stripe_live_secret_key' => 'sk_live_',
        'stripe_live_webhook_secret' => 'whsec_',
    ];

    private const OPENPAY_SECRET_KEYS = [
        'openpay_sandbox_private_key',
        'openpay_production_private_key',
    ];

    private const OPENPAY_PUBLIC_KEYS = [
        'openpay_sandbox_public_key',
        'openpay_production_public_key',
    ];

    private const OPENPAY_MERCHANT_KEYS = [
        'openpay_sandbox_merchant_id',
        'openpay_production_merchant_id',
    ];

    /** Llaves públicas OpenPay deben empezar con pk_ (sandbox o producción). */
    private const OPENPAY_PUBLIC_PREFIX_RULES = [
        'openpay_sandbox_public_key' => 'pk_',
        'openpay_production_public_key' => 'pk_',
    ];

    /** Llaves privadas OpenPay (API) suelen empezar con sk_. */
    private const OPENPAY_PRIVATE_PREFIX_RULES = [
        'openpay_sandbox_private_key' => 'sk_',
        'openpay_production_private_key' => 'sk_',
    ];

    public function stripe(Request $request)
    {
        try {
            $data = [
                'stripe_mode' => SystemSetting::get('stripe_mode', 'test'),
            ];

            foreach (self::PUBLISHABLE_KEYS as $key) {
                $data[$key] = SystemSetting::get($key, '');
            }

            foreach (self::SECRET_KEYS as $key) {
                $raw = SystemSetting::getEncrypted($key, '');
                $data[$key] = $this->mask($raw);
            }

            return ApiResponseHelper::apiSuccess(200, 'Configuración de Stripe obtenida', $data);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la configuración de Stripe', $e->getMessage(), 500);
        }
    }

    public function updateStripe(Request $request)
    {
        try {
            // Validate prefixes for non-empty fields
            $errors = [];
            foreach (self::PREFIX_RULES as $field => $prefix) {
                $value = $request->input($field);
                if (!empty($value) && !str_starts_with($value, $prefix)) {
                    $errors[$field] = ["El campo {$field} debe comenzar con {$prefix}"];
                }
            }

            if (!empty($errors)) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Error de validación',
                    'errors' => $errors,
                ], 422);
            }

            // Update stripe_mode
            if ($request->has('stripe_mode')) {
                $mode = $request->input('stripe_mode');
                if (in_array($mode, ['test', 'live'])) {
                    SystemSetting::set('stripe_mode', $mode);
                }
            }

            // Update publishable keys (plain text)
            foreach (self::PUBLISHABLE_KEYS as $key) {
                $value = $request->input($key);
                if (!empty($value)) {
                    SystemSetting::set($key, $value);
                }
            }

            // Update secret keys (encrypted)
            foreach (self::SECRET_KEYS as $key) {
                $value = $request->input($key);
                if (!empty($value)) {
                    SystemSetting::setEncrypted($key, $value);
                }
            }

            return ApiResponseHelper::apiSuccess(200, 'Configuración de Stripe actualizada correctamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar la configuración de Stripe', $e->getMessage(), 500);
        }
    }

    public function publishableKey(Request $request)
    {
        try {
            $mode = SystemSetting::get('stripe_mode', 'test');
            $key = SystemSetting::get("stripe_{$mode}_publishable_key", '');

            return ApiResponseHelper::apiSuccess(200, 'Llave publicable obtenida', [
                'publishable_key' => $key,
                'mode' => $mode,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la llave publicable', $e->getMessage(), 500);
        }
    }

    /**
     * Configuración OpenPay (tienda). Documentación: https://documents.openpay.mx/docs/api/
     */
    public function openpay(Request $request)
    {
        try {
            $data = [
                'openpay_mode' => SystemSetting::get('openpay_mode', 'sandbox'),
            ];

            foreach (self::OPENPAY_MERCHANT_KEYS as $key) {
                $data[$key] = SystemSetting::get($key, '');
            }

            foreach (self::OPENPAY_PUBLIC_KEYS as $key) {
                $data[$key] = SystemSetting::get($key, '');
            }

            foreach (self::OPENPAY_SECRET_KEYS as $key) {
                $raw = SystemSetting::getEncrypted($key, '');
                $data[$key] = $this->mask($raw);
            }

            return ApiResponseHelper::apiSuccess(200, 'Configuración de OpenPay obtenida', $data);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la configuración de OpenPay', $e->getMessage(), 500);
        }
    }

    public function updateOpenpay(Request $request)
    {
        try {
            $errors = [];

            foreach (self::OPENPAY_PUBLIC_PREFIX_RULES as $field => $prefix) {
                $value = $request->input($field);
                if (! empty($value) && ! str_starts_with($value, $prefix)) {
                    $errors[$field] = ["El campo {$field} debe comenzar con {$prefix}"];
                }
            }

            foreach (self::OPENPAY_PRIVATE_PREFIX_RULES as $field => $prefix) {
                $value = $request->input($field);
                if (! empty($value) && ! str_starts_with($value, $prefix)) {
                    $errors[$field] = ["El campo {$field} debe comenzar con {$prefix}"];
                }
            }

            foreach (self::OPENPAY_MERCHANT_KEYS as $field) {
                $value = $request->input($field);
                if (! empty($value) && ! preg_match('/^[a-zA-Z0-9_-]{4,}$/', $value)) {
                    $errors[$field] = ['El ID de comercio no tiene un formato válido'];
                }
            }

            if (! empty($errors)) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Error de validación',
                    'errors' => $errors,
                ], 422);
            }

            if ($request->has('openpay_mode')) {
                $mode = $request->input('openpay_mode');
                if (in_array($mode, ['sandbox', 'production'], true)) {
                    SystemSetting::set('openpay_mode', $mode);
                }
            }

            foreach (self::OPENPAY_MERCHANT_KEYS as $key) {
                $value = $request->input($key);
                if ($value !== null && $value !== '') {
                    SystemSetting::set($key, $value);
                }
            }

            foreach (self::OPENPAY_PUBLIC_KEYS as $key) {
                $value = $request->input($key);
                if (! empty($value)) {
                    SystemSetting::set($key, $value);
                }
            }

            foreach (self::OPENPAY_SECRET_KEYS as $key) {
                $value = $request->input($key);
                if (! empty($value)) {
                    SystemSetting::setEncrypted($key, $value);
                }
            }

            return ApiResponseHelper::apiSuccess(200, 'Configuración de OpenPay actualizada correctamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar la configuración de OpenPay', $e->getMessage(), 500);
        }
    }

    /**
     * Datos no sensibles para el checkout (OpenPay.js en el navegador).
     */
    public function openpayCheckoutPublic(Request $request)
    {
        try {
            $mode = SystemSetting::get('openpay_mode', 'sandbox');
            $suffix = $mode === 'production' ? 'production' : 'sandbox';

            $merchantId = trim((string) SystemSetting::get("openpay_{$suffix}_merchant_id", ''));
            $publicKey = trim((string) SystemSetting::get("openpay_{$suffix}_public_key", ''));

            return ApiResponseHelper::apiSuccess(200, 'Configuración pública OpenPay', [
                'merchant_id' => $merchantId,
                'public_key' => $publicKey,
                'sandbox' => $mode !== 'production',
                'available' => $merchantId !== '' && $publicKey !== '',
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al leer OpenPay', $e->getMessage(), 500);
        }
    }

    /**
     * Checkout boutique público: qué métodos de pago están habilitados (sin auth).
     * Contrato esperado por el front: data.methods + data.openpay.
     */
    public function boutiquePaymentMethodsPublic(Request $request)
    {
        try {
            $stripeMode = SystemSetting::get('stripe_mode', 'test');
            if (! in_array($stripeMode, ['test', 'live'], true)) {
                $stripeMode = 'test';
            }
            $pkStripe = trim((string) SystemSetting::get("stripe_{$stripeMode}_publishable_key", ''));
            $stripeOn = $pkStripe !== '';

            $openpayMode = SystemSetting::get('openpay_mode', 'sandbox');
            $suffixOp = $openpayMode === 'production' ? 'production' : 'sandbox';
            $merchantId = trim((string) SystemSetting::get("openpay_{$suffixOp}_merchant_id", ''));
            $publicKey = trim((string) SystemSetting::get("openpay_{$suffixOp}_public_key", ''));
            $openpayOn = $merchantId !== '' && $publicKey !== '';

            $transferencia = filter_var(SystemSetting::get('boutique_checkout_transferencia', '1'), FILTER_VALIDATE_BOOLEAN);
            $sucursal = filter_var(SystemSetting::get('boutique_checkout_sucursal', '1'), FILTER_VALIDATE_BOOLEAN);

            $openpayPayload = [
                'merchant_id' => $merchantId,
                'public_key' => $publicKey,
                'sandbox' => $openpayMode !== 'production',
                'available' => $openpayOn,
            ];

            return ApiResponseHelper::apiSuccess(200, 'Métodos de pago boutique', [
                'methods' => [
                    'stripe' => $stripeOn,
                    'openpay' => $openpayOn,
                    'transferencia' => $transferencia,
                    'sucursal' => $sucursal,
                ],
                'openpay' => $openpayPayload,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al leer métodos de pago boutique', $e->getMessage(), 500);
        }
    }

    /**
     * Checkout boutique público: tipos de embalaje para cotización (sin auth).
     * Si no hay catálogo en BD, se devuelve un tipo por defecto compatible con el checkout.
     */
    public function boutiqueShippingPackageTypesPublic(Request $request)
    {
        try {
            $types = [[
                'id' => 'default',
                'label' => 'Embalaje estándar',
                'package_kind' => 'box',
                'weight' => 1.0,
                'length' => 30.0,
                'width' => 30.0,
                'height' => 20.0,
                'extra_cost' => 0.0,
            ]];

            return ApiResponseHelper::apiSuccess(200, 'Tipos de embalaje boutique', ['types' => $types]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al leer tipos de embalaje', $e->getMessage(), 500);
        }
    }

    private function mask(string $value): string
    {
        if (empty($value) || strlen($value) <= 4) {
            return $value;
        }
        return '••••••••' . substr($value, -4);
    }
}
