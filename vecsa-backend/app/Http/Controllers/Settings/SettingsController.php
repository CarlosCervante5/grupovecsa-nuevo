<?php

namespace App\Http\Controllers\Settings;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\Media\ImageAiProcessingService;
use App\Support\BoutiqueCheckoutLegalPages;
use App\Support\BoutiqueCheckoutPaymentMethods;
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
        'openpay_webhook_password',
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

            $data['openpay_webhook_user'] = SystemSetting::get('openpay_webhook_user', '');
            $data['openpay_webhook_url'] = app(\App\Services\Boutique\OpenPayService::class)->webhookUrl();
            $data['boutique_checkout_openpay'] = filter_var(
                SystemSetting::get('boutique_checkout_openpay', '1'),
                FILTER_VALIDATE_BOOLEAN
            );
            $data['keys_configured'] = BoutiqueCheckoutPaymentMethods::openpayKeysConfigured();

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

            $webhookUser = $request->input('openpay_webhook_user');
            if ($webhookUser !== null && $webhookUser !== '') {
                SystemSetting::set('openpay_webhook_user', (string) $webhookUser);
            }

            $webhookPassword = $request->input('openpay_webhook_password');
            if ($webhookPassword !== null && $webhookPassword !== '') {
                SystemSetting::setEncrypted('openpay_webhook_password', (string) $webhookPassword);
            }

            if ($request->has('boutique_checkout_openpay')) {
                $enablePasarela = filter_var($request->input('boutique_checkout_openpay'), FILTER_VALIDATE_BOOLEAN);
                if ($enablePasarela && ! BoutiqueCheckoutPaymentMethods::openpayKeysConfigured()) {
                    return ApiResponseHelper::apiError(
                        'No se puede activar la pasarela sin credenciales completas (comercio, llave pública y privada).',
                        null,
                        422,
                        'OPENPAY_KEYS_MISSING'
                    );
                }
                SystemSetting::set('boutique_checkout_openpay', $enablePasarela ? '1' : '0');
            }

            return ApiResponseHelper::apiSuccess(200, 'Configuración de OpenPay actualizada correctamente', [
                'boutique_checkout_openpay' => filter_var(
                    SystemSetting::get('boutique_checkout_openpay', '1'),
                    FILTER_VALIDATE_BOOLEAN
                ),
                'keys_configured' => BoutiqueCheckoutPaymentMethods::openpayKeysConfigured(),
            ]);
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
            $openpay = BoutiqueCheckoutPaymentMethods::publicPayload()['openpay'];

            return ApiResponseHelper::apiSuccess(200, 'Configuración pública OpenPay', $openpay);
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
            return ApiResponseHelper::apiSuccess(200, 'Métodos de pago boutique', BoutiqueCheckoutPaymentMethods::publicPayload());
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al leer métodos de pago boutique', $e->getMessage(), 500);
        }
    }

    /**
     * Admin tienda: lectura de métodos de pago del checkout (misma carga que el endpoint público).
     */
    public function boutiqueCheckoutPaymentMethodsConfig(Request $request)
    {
        try {
            return ApiResponseHelper::apiSuccess(200, 'Métodos de pago checkout', BoutiqueCheckoutPaymentMethods::adminConfigPayload());
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al leer métodos de pago', $e->getMessage(), 500);
        }
    }

    /**
     * Admin tienda: activar/desactivar transferencia y pago en sucursal en el checkout boutique.
     */
    public function updateBoutiqueCheckoutPaymentMethods(Request $request)
    {
        try {
            $data = $request->validate([
                'boutique_checkout_openpay' => 'sometimes|boolean',
                'boutique_checkout_transferencia' => 'required|boolean',
                'boutique_checkout_sucursal' => 'required|boolean',
            ]);

            if (array_key_exists('boutique_checkout_openpay', $data)) {
                if ($data['boutique_checkout_openpay'] && ! BoutiqueCheckoutPaymentMethods::openpayKeysConfigured()) {
                    return ApiResponseHelper::apiError(
                        'OpenPay no tiene credenciales configuradas. Complétalas en «Pagos OpenPay».',
                        null,
                        422,
                        'OPENPAY_KEYS_MISSING'
                    );
                }
                SystemSetting::set('boutique_checkout_openpay', $data['boutique_checkout_openpay'] ? '1' : '0');
            }

            SystemSetting::set('boutique_checkout_transferencia', $data['boutique_checkout_transferencia'] ? '1' : '0');
            SystemSetting::set('boutique_checkout_sucursal', $data['boutique_checkout_sucursal'] ? '1' : '0');

            $payload = BoutiqueCheckoutPaymentMethods::adminConfigPayload();
            $message = 'Métodos de pago actualizados';
            if (! BoutiqueCheckoutPaymentMethods::hasAnyEnabledMethod()) {
                $message .= '. Ningún método en checkout: los clientes verán la opción de contactar ventas por WhatsApp.';
            }

            return ApiResponseHelper::apiSuccess(200, $message, $payload);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al guardar métodos de pago', $e->getMessage(), 500);
        }
    }

    /**
     * Admin tienda: rutas/URLs de condiciones, privacidad y devoluciones en el checkout boutique.
     */
    public function updateBoutiqueCheckoutLegalPages(Request $request)
    {
        try {
            $data = $request->validate([
                'boutique_checkout_legal_terms_url' => 'nullable|string|max:500',
                'boutique_checkout_legal_privacy_url' => 'nullable|string|max:500',
                'boutique_checkout_legal_returns_url' => 'nullable|string|max:500',
            ]);

            $map = [
                'boutique_checkout_legal_terms_url' => BoutiqueCheckoutLegalPages::SETTING_TERMS,
                'boutique_checkout_legal_privacy_url' => BoutiqueCheckoutLegalPages::SETTING_PRIVACY,
                'boutique_checkout_legal_returns_url' => BoutiqueCheckoutLegalPages::SETTING_RETURNS,
            ];

            foreach ($map as $inputKey => $settingKey) {
                if (! array_key_exists($inputKey, $data)) {
                    continue;
                }
                $trim = trim((string) ($data[$inputKey] ?? ''));
                if ($trim === '') {
                    SystemSetting::set($settingKey, '');

                    continue;
                }
                if (! BoutiqueCheckoutLegalPages::isAllowedUrl($trim)) {
                    return ApiResponseHelper::apiError(
                        "URL o ruta no válida en {$inputKey}. Usa una ruta que empiece con / (ej. /condiciones-uso) o una URL https:// completa.",
                        null,
                        422,
                        'INVALID_LEGAL_PAGE_URL'
                    );
                }
                $normalized = str_starts_with($trim, 'http') ? $trim : (str_starts_with($trim, '/') ? $trim : '/'.$trim);
                SystemSetting::set($settingKey, $normalized);
            }

            return ApiResponseHelper::apiSuccess(
                200,
                'Enlaces legales del checkout actualizados',
                BoutiqueCheckoutPaymentMethods::adminConfigPayload()
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al guardar enlaces legales', $e->getMessage(), 500);
        }
    }

    public function updateBoutiqueTransferBankDetails(Request $request)
    {
        try {
            $data = $request->validate([
                'boutique_transfer_bank_name' => 'nullable|string|max:255',
                'boutique_transfer_account_holder' => 'nullable|string|max:255',
                'boutique_transfer_clabe' => 'nullable|string|max:18',
                'boutique_transfer_account_number' => 'nullable|string|max:32',
                'boutique_transfer_instructions' => 'nullable|string|max:2000',
            ]);

            foreach ($data as $key => $value) {
                SystemSetting::set($key, is_string($value) ? trim($value) : '');
            }

            return ApiResponseHelper::apiSuccess(
                200,
                'Datos bancarios actualizados',
                BoutiqueCheckoutPaymentMethods::adminConfigPayload()
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al guardar datos bancarios', $e->getMessage(), 500);
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

    /**
     * Gemini API (Google AI Studio) — edición de fotos vehículos / boutique vía Gemini.
     */
    public function geminiImageAi(Request $request)
    {
        try {
            $rawKey = trim((string) SystemSetting::getEncrypted('gemini_api_key', ''));

            return ApiResponseHelper::apiSuccess(200, 'Configuración Gemini — edición de fotos', [
                'gemini_api_key' => $rawKey !== '' ? $this->mask($rawKey) : '',
                'gemini_image_model' => SystemSetting::get('gemini_image_model', ''),
                'image_ai_enabled' => filter_var(SystemSetting::get('image_ai_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
                'default_model_hint' => ImageAiProcessingService::DEFAULT_IMAGE_MODEL,
                'configured' => $rawKey !== '',
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la configuración de Gemini', $e->getMessage(), 500);
        }
    }

    public function updateGeminiImageAi(Request $request)
    {
        try {
            $validated = $request->validate([
                'gemini_api_key' => 'nullable|string|max:512',
                'gemini_image_model' => 'nullable|string|max:160',
                'image_ai_enabled' => 'sometimes|boolean',
            ]);

            if (array_key_exists('image_ai_enabled', $validated)) {
                SystemSetting::set('image_ai_enabled', ! empty($validated['image_ai_enabled']) ? '1' : '0');
            }

            if (array_key_exists('gemini_image_model', $validated)) {
                $model = trim((string) $validated['gemini_image_model']);
                if ($model !== '' && ! preg_match('/^[a-zA-Z0-9._\-]+$/', $model)) {
                    return response()->json([
                        'status' => 422,
                        'message' => 'Error de validación',
                        'errors' => ['gemini_image_model' => ['El nombre del modelo tiene caracteres no permitidos']],
                    ], 422);
                }
                SystemSetting::set('gemini_image_model', $model);
            }

            $key = $request->input('gemini_api_key');
            if ($key !== null && $key !== '' && ! str_starts_with((string) $key, '•••')) {
                if (strlen((string) $key) < 20) {
                    return response()->json([
                        'status' => 422,
                        'message' => 'Error de validación',
                        'errors' => ['gemini_api_key' => ['La llave API parece inválida (demasiado corta)']],
                    ], 422);
                }
                SystemSetting::setEncrypted('gemini_api_key', trim((string) $key));
            }

            return ApiResponseHelper::apiSuccess(200, 'Configuración Gemini actualizada');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar Gemini', $e->getMessage(), 500);
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
