<?php

namespace OpenpayStores\Services\PaymentSettings;

use OpenpayStores\Includes\OpenpayClient;
use WC_Admin_Settings;
use WC_Logger;
use Openpay\Data\OpenpayApi;
use OpenpayStores\Includes\OpenpayStoresErrorHandler;

// No debe heredar de WC_Payment_Gateway
class OpenpayPaymentSettingsValidation
{
    private $logger;
    private $gateway_id;
    private $openpayClientClass;

    /**
     * El constructor requiere el logger y el ID de la pasarela.
     */
    public function __construct(WC_Logger $logger, string $gateway_id)
    {
        $this->logger = $logger;
        $this->gateway_id = $gateway_id;
    }

    /**
     * Valida las credenciales de Openpay haciendo una llamada a la API. Y devuelve la instancia de OpenpayApi si son válidas.
     *
     * @param array $settings Los ajustes enviados desde el formulario.
     * @return bool True si las credenciales son válidas, false en caso contrario.
     */
    public function validateOpenpayCredentials(array $settings)
    {
        $this->logger->info('Datos recibidos para validación: ' . json_encode($settings));

        $is_sandbox = !empty($settings['woocommerce_' . $this->gateway_id . '_sandbox']);

        $this->logger->info('RESULTADO DE $is_sandbox: ' . ($is_sandbox ? 'true' : 'false'));

        $mode = $is_sandbox ? 'test' : 'live';

        // Y usamos el ID para leer las otras claves
        $merchant_id = $settings['woocommerce_' . $this->gateway_id . '_' . $mode . '_merchant_id'] ?? '';
        $private_key = $settings['woocommerce_' . $this->gateway_id . '_' . $mode . '_private_key'] ?? '';
        $country = $settings['woocommerce_' . $this->gateway_id . '_country'] ?? 'MX';

        if (empty($merchant_id) || empty($private_key)) {
            \WC_Admin_Settings::add_error('Las credenciales para el modo ' . ($is_sandbox ? 'Sandbox' : 'Producción') . ' no pueden estar vacías.');

            // Añadimos un mensaje al log
            $this->logger->warning('Intento de guardar credenciales vacías para el modo ' . $mode);
            return null;
        }

        try {
            $openpay = $this->createOpenpayApiInstance($merchant_id, $private_key, $country, $is_sandbox);
            $webhooks = $openpay->webhooks->getList(['limit' => 1]); // Llamada de prueba a la API
            $this->logger->info('Credenciales validadas exitosamente para el modo ' . $mode);
            $this->logger->info('Respuesta de validación de credenciales (Webhooks): ' . json_encode($webhooks));
            return $openpay;
        } catch (\Exception $e) {
            \WC_Admin_Settings::add_error('Error al validar las credenciales de Openpay: ' . esc_html($e->getMessage()));

            // Registramos el error completo en el log para poder depurarlo
            $this->logger->error('Fallo en la validación de API: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Envuelve la llamada estática para que podamos sobrescribirla en las pruebas.
     * @return \Openpay\Data\OpenpayApi
     */
    protected function createOpenpayApiInstance(string $merchant_id, string $private_key, string $country, bool $is_sandbox): OpenpayApi
    {
        $openpayClient = OpenpayStoresErrorHandler::catchOpenpayStoreError(function () use($merchant_id, $private_key, $country, $is_sandbox) {
            return OpenpayClient::getInstance($merchant_id, $private_key, $country, $is_sandbox);
         });
         return $openpayClient;
    }

    /**
     * Valida que la moneda de la tienda sea compatible.
     *
     * @param array $allowed_currencies Monedas permitidas.
     * @return bool True si la moneda es válida, false en caso contrario.
     */
    public function validateCurrency(array $allowed_currencies): bool
    {
        $store_currency = get_woocommerce_currency();
        if (!in_array($store_currency, $allowed_currencies)) {
            \WC_Admin_Settings::add_error('La moneda actual de la tienda (' . $store_currency . ') no es compatible con la configuración regional de Openpay.');
            return false;
        }
        return true;
    }
}
