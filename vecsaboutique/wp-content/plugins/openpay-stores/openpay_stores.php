<?php

use OpenpayStores\OpenpayStoresGateway;
use OpenpayStores\Includes\OpenpayStoresGateway_Blocks_Support;
use OpenpayStores\Services\OpenpayWebhookProcessorService;

/**
 * Plugin Name: Openpay Stores Plugin
 * Plugin URI: http://www.openpay.mx/docs/plugins/woocommerce.html
 * Description: Provides a cash payment method with Openpay for WooCommerce.
 * Version: 2.0.1
 * Author: Openpay
 * Author URI: http://www.openpay.mx
 * Developer: Openpay
 * Text Domain: openpay-stores
 *
 * WC requires at least: 3.0
 * WC tested up to: 8.5.2
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Openpay Docs: http://www.openpay.mx/docs/
 */

// Evita el acceso directo al archivo.
if (!defined('ABSPATH')) {
    exit;
}

// ***** INCLUIR EL AUTOLOADER DE COMPOSER *****
// Esto le da a WordPress acceso a todas tus clases con namespace.
require_once __DIR__ . '/vendor/autoload.php';


// Hook para inicializar la pasarela
add_action('plugins_loaded', 'OpenpayStoresGateway_init_gateway_class');

// Hook para registrar los scripts de admin
add_action('admin_enqueue_scripts', 'OpenpayStoresGateway_admin_enqueue');

// Hook para cargar el soporte de bloques
add_action('woocommerce_blocks_loaded', 'OpenpayStoresGateway_blocks_support');

// Hook para añadir el enlace de "Ajustes" en la página de plugins
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'OpenpayStoresGateway_settings_link');

// Hook para la compatibilidad con tablas de órdenes de WooCommerce
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

//Hook para llamar scripts personalizados
add_action('wp_enqueue_scripts', 'payment_scripts');

add_action('woocommerce_thankyou', 'openpay_stores_custom_thankyou_content', 1);

/**
 * Inicializa la pasarela de pago.
 */
function OpenpayStoresGateway_init_gateway_class()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    // Esto asegura que el "trabajador" (Processor)
    // se registre en CADA carga de página, incluyendo WP-Cron.
    new OpenpayWebhookProcessorService();

    // Este es el único filtro que necesitas para registrar la pasarela
    add_filter('woocommerce_payment_gateways', 'OpenpayStoresGateway_add_gateway');
}

/**
 * Registra la clase de la pasarela en WooCommerce.
 */
function OpenpayStoresGateway_add_gateway($gateways)
{
    $gateways[] = OpenpayStoresGateway::class;
    return $gateways;
}

/**
 * Registra el script de JavaScript para el panel de administración.
 */
function OpenpayStoresGateway_admin_enqueue($hook)
{
    wp_enqueue_script('openpay_stores_admin_form', plugins_url('assets/js/admin.js', __FILE__), array('jquery'), '1.0.2', true);
}

/**
 * Añade el enlace de "Ajustes" a la lista de acciones del plugin.
 */
function OpenpayStoresGateway_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=openpay_stores') . '">' . __('Ajustes', 'openpay_stores') . '</a>';
    array_unshift($links, $settings_link); // unshift lo pone al principio, es más común
    return $links;
}

/**
 * Registra la integración con los bloques de WooCommerce.
 */
function OpenpayStoresGateway_blocks_support()
{
    if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        return;
    }

    // Ya no se necesita un 'require_once' aquí porque el autoloader de Composer se encarga de ello.

    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
            // Usamos la clase con su namespace completo
            $payment_method_registry->register(new OpenpayStoresGateway_Blocks_Support());
        }
    );
}

function payment_scripts()
{
    // Solo en el checkout y si NO hay bloques de checkout activos
    if (!is_checkout() || (function_exists('has_block') && has_block('woocommerce/checkout'))) {
        return;
    }
    wp_enqueue_script('openpay_new_checkout', plugins_url('assets/js/openpay_new_checkout.js', __FILE__), array('jquery'), '', true);
}

/**
 * MUESTRA LA PLANTILLA EN LA PÁGINA DE CONFIRMACIÓN (UI)
 * Funciona para Checkout Clásico y Bloques.
 */
function openpay_stores_custom_thankyou_content($order_id)
{
    if (!$order_id)
        return;

    $order = wc_get_order($order_id);

    if ($order && $order->get_payment_method() === 'openpay_stores') {
        $template_path = plugin_dir_path(__FILE__) . 'templates/woocommerce/checkout/thankyou.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }
}

/**
 * FILTRA LA PLANTILLA PARA EL CORREO ELECTRÓNICO
 * Solo se encarga de interceptar el email de "Pedido en espera".
 */
add_filter('woocommerce_locate_template', 'openpay_stores_custom_email_template', 10, 3);

function openpay_stores_custom_email_template($template, $template_name, $template_path)
{
    // Solo nos interesa el email de pedido en espera
    if ($template_name !== 'emails/customer-on-hold-order.php') {
        return $template;
    }

    // Verificamos si estamos en un contexto de email
    if (did_action('woocommerce_email_header')) {
        $email = WC()->mailer()->get_emails();
        foreach ($email as $email_obj) {
            if (isset($email_obj->object) && is_a($email_obj->object, 'WC_Order')) {
                $order = $email_obj->object;
                if ($order->get_payment_method() === 'openpay_stores') {
                    // Ruta al archivo del email en tu plugin
                    $plugin_template = plugin_dir_path(__FILE__) . 'templates/woocommerce/' . $template_name;
                    if (file_exists($plugin_template)) {
                        return $plugin_template;
                    }
                }
            }
        }
    }

    return $template;
}