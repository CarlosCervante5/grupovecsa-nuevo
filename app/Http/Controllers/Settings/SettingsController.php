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

    private function mask(string $value): string
    {
        if (empty($value) || strlen($value) <= 4) {
            return $value;
        }
        return '••••••••' . substr($value, -4);
    }
}
