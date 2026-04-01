<?php

namespace OpenpayStores\Includes;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class OpenpayStoresGateway_Blocks_Support extends AbstractPaymentMethodType
{

    /**
     * Nombre interno del método de pago.
     */
    protected $name = 'openpay_stores';

    /**
     * Inicialización de los ajustes.
     */
    public function initialize()
    {
        $this->settings = get_option("woocommerce_{$this->name}_settings", array());
        // Esto aparecerá en el archivo wp-content/debug.log
        error_log('DEBUG Openpay: Ajustes cargados -> ' . print_r($this->settings, true));
        add_action('woocommerce_rest_checkout_process_payment_with_context', function ($context, $result) {
            $logger = wc_get_logger();
            if ($context->payment_method === 'openpay_stores') {
                $logger->debug('CONTEXT->FRONT_PAYMENT_DATA - ' . json_encode($context->payment_data));
            }
        }, 10, 2);
    }

    /**
     * Verifica si el método está activo.
     */
    public function is_active()
    {
        return !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'];
    }

    /**
     * Registro y obtención de los handles de scripts y estilos.
     */
    public function get_payment_method_script_handles()
    {
        $plugin_url = plugin_dir_url(dirname(__FILE__, 1));
        $plugin_path = plugin_dir_path(dirname(__FILE__, 1));
        $assets_path = $plugin_path . 'blocks/checkout-form/build/index.asset.php';

        $version = "2.0.1";
        $dependencies = array();

        if (file_exists($assets_path)) {
            $asset = require $assets_path;
            $version = $asset['version'] ?? $version;
            $dependencies = $asset['dependencies'] ?? $dependencies;
        }

        // REGISTRO DEL SCRIPT (JS/REACT)
        wp_register_script(
            'openpay-stores-blocks-integration',
            $plugin_url . '/blocks/checkout-form/build/index.js',
            $dependencies,
            $version,
            true
        );

        // REGISTRO DEL ESTILO ESPECÍFICO PARA BLOQUES
        wp_register_style(
            'openpay-stores-blocks-styles',
            $plugin_url . 'assets/css/openpay-store-blocks-checkout.css',
            array(),
            $version
        );

        return array('openpay-stores-blocks-integration');
    }

    /**
     * Envío de datos desde PHP al frontend de React.
     */
    public function get_payment_method_data()
    {
        $titles = [
            'MX' => __('Pago con efectivo en tiendas', 'openpay_stores'),
            'CO' => __('Pago con efectivo', 'openpay_stores'),
            'PE' => __('Pago en agencias', 'openpay_stores'),
        ];

        $raw_country = $this->settings['country'] ?? 'MX';
        $country = strtoupper(trim($raw_country));

        $current_title = isset($titles[$country]) ? $titles[$country] : $titles['MX'];

        $images_url = plugin_dir_url(dirname(__FILE__, 1)) . 'assets/images/';

        return array(
            'country' => $country,
            'images_dir' => $images_url,
            'title' => $current_title,
        );
    }

}