<?php
/**
 * Plugin Name: WooCommerce Auto Discount
 * Plugin URI: https://tusitio.com
 * Description: Aplica descuentos automáticos a productos por categoría o etiqueta y muestra badge de oferta
 * Version: 1.0.0
 * Author: Tu Nombre
 * Author URI: https://tusitio.com
 * Text Domain: woo-auto-discount
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 10.1
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Si este archivo es llamado directamente, abortar.
if (!defined('WPINC')) {
    die;
}

// Definir constantes
define('WOO_AUTO_DISCOUNT_VERSION', '1.0.0');
define('WOO_AUTO_DISCOUNT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOO_AUTO_DISCOUNT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Clase principal del plugin
 */
class WooAutoDiscount {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Inicializar hooks
     */
    private function init_hooks() {
        // Hook de activación
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // Hook de desactivación
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Admin
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Frontend
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        
        // Descuentos en carrito
        add_action('woocommerce_cart_calculate_fees', array($this, 'apply_cart_discount'));
        
        // Badge de oferta en productos
        add_action('woocommerce_before_shop_loop_item_title', array($this, 'show_discount_badge'), 10);
        add_action('woocommerce_before_single_product_summary', array($this, 'show_discount_badge'), 10);
        
        // Mostrar precio con descuento
        add_filter('woocommerce_get_price_html', array($this, 'show_discounted_price'), 10, 2);
        
        // Soporte para productos variables
        add_filter('woocommerce_available_variation', array($this, 'add_variation_discount_data'), 10, 3);
        add_action('wp_footer', array($this, 'add_variation_discount_script'));
    }
    
    /**
     * Activación del plugin
     */
    public function activate() {
        // Crear opciones por defecto
        $default_options = array(
            'enabled' => 'yes',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'apply_to' => 'category',
            'selected_categories' => array(),
            'selected_tags' => array(),
            'badge_text' => '¡OFERTA!',
            'badge_style' => 'modern',
            'badge_color' => '#ff0000',
            'badge_text_color' => '#ffffff',
            'show_badge' => 'yes',
            'show_discount_amount' => 'yes'
        );
        
        add_option('woo_auto_discount_options', $default_options);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Desactivación del plugin
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Agregar menú de administración
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Descuentos Automáticos', 'woo-auto-discount'),
            __('Descuentos Auto', 'woo-auto-discount'),
            'manage_woocommerce',
            'woo-auto-discount',
            array($this, 'settings_page'),
            'dashicons-tag',
            56
        );
    }
    
    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        register_setting('woo_auto_discount_options', 'woo_auto_discount_options', array($this, 'sanitize_settings'));
    }
    
    /**
     * Sanitizar configuraciones
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['enabled'] = isset($input['enabled']) ? 'yes' : 'no';
        $sanitized['discount_type'] = sanitize_text_field($input['discount_type']);
        $sanitized['discount_value'] = floatval($input['discount_value']);
        $sanitized['apply_to'] = sanitize_text_field($input['apply_to']);
        $sanitized['selected_categories'] = isset($input['selected_categories']) ? array_map('intval', $input['selected_categories']) : array();
        $sanitized['selected_tags'] = isset($input['selected_tags']) ? array_map('intval', $input['selected_tags']) : array();
        $sanitized['badge_text'] = sanitize_text_field($input['badge_text']);
        $sanitized['badge_style'] = sanitize_text_field($input['badge_style']);
        $sanitized['badge_color'] = sanitize_hex_color($input['badge_color']);
        $sanitized['badge_text_color'] = sanitize_hex_color($input['badge_text_color']);
        $sanitized['show_badge'] = isset($input['show_badge']) ? 'yes' : 'no';
        $sanitized['show_discount_amount'] = isset($input['show_discount_amount']) ? 'yes' : 'no';
        
        return $sanitized;
    }
    
    /**
     * Página de configuración
     */
    public function settings_page() {
        include WOO_AUTO_DISCOUNT_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_woo-auto-discount' !== $hook) {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        wp_enqueue_style(
            'woo-auto-discount-admin',
            WOO_AUTO_DISCOUNT_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WOO_AUTO_DISCOUNT_VERSION
        );
        
        wp_enqueue_script(
            'woo-auto-discount-admin',
            WOO_AUTO_DISCOUNT_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            WOO_AUTO_DISCOUNT_VERSION,
            true
        );
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style(
            'woo-auto-discount-frontend',
            WOO_AUTO_DISCOUNT_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            WOO_AUTO_DISCOUNT_VERSION
        );
    }
    
    /**
     * Verificar si un producto califica para descuento
     */
    public function product_qualifies_for_discount($product_id) {
        $options = get_option('woo_auto_discount_options');
        
        if ($options['enabled'] !== 'yes') {
            return false;
        }
        
        if ($options['apply_to'] === 'category') {
            $product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
            $selected_categories = $options['selected_categories'];
            
            if (!empty(array_intersect($product_categories, $selected_categories))) {
                return true;
            }
        } elseif ($options['apply_to'] === 'tag') {
            $product_tags = wp_get_post_terms($product_id, 'product_tag', array('fields' => 'ids'));
            $selected_tags = $options['selected_tags'];
            
            if (!empty(array_intersect($product_tags, $selected_tags))) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Calcular descuento para un producto
     */
    public function calculate_discount($price) {
        $options = get_option('woo_auto_discount_options');
        
        if ($options['discount_type'] === 'percentage') {
            return ($price * $options['discount_value']) / 100;
        } else {
            return $options['discount_value'];
        }
    }
    
    /**
     * Aplicar descuento en el carrito
     */
    public function apply_cart_discount($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        
        $options = get_option('woo_auto_discount_options');
        
        if ($options['enabled'] !== 'yes') {
            return;
        }
        
        $total_discount = 0;
        $discounted_products = array();
        
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            
            if ($this->product_qualifies_for_discount($product_id)) {
                $product_price = $cart_item['data']->get_price();
                $quantity = $cart_item['quantity'];
                $discount = $this->calculate_discount($product_price) * $quantity;
                
                $total_discount += $discount;
                $discounted_products[] = $cart_item['data']->get_name();
            }
        }
        
        if ($total_discount > 0) {
            $discount_label = sprintf(
                __('Descuento aplicado (%s)', 'woo-auto-discount'),
                implode(', ', array_unique($discounted_products))
            );
            
            $cart->add_fee($discount_label, -$total_discount);
        }
    }
    
    /**
     * Mostrar badge de descuento
     */
    public function show_discount_badge() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        $options = get_option('woo_auto_discount_options');
        
        if ($options['show_badge'] !== 'yes') {
            return;
        }
        
        if ($this->product_qualifies_for_discount($product->get_id())) {
            $badge_text = $options['badge_text'];
            
            if ($options['show_discount_amount'] === 'yes') {
                if ($options['discount_type'] === 'percentage') {
                    $badge_text .= ' -' . $options['discount_value'] . '%';
                } else {
                    $badge_text .= ' -$' . number_format($options['discount_value'], 2);
                }
            }
            
            $style = $options['badge_style'];
            
            echo '<span class="woo-auto-discount-badge woo-auto-discount-badge-' . esc_attr($style) . '" style="background-color: ' . esc_attr($options['badge_color']) . '; color: ' . esc_attr($options['badge_text_color']) . ';">' . esc_html($badge_text) . '</span>';
        }
    }
    
    /**
     * Mostrar precio con descuento tachado
     */
    public function show_discounted_price($price, $product) {
        // Para productos variables, solo modificar el precio del padre si no está en página de producto
        if ($product->is_type('variable') && is_product()) {
            return $price; // El precio de las variaciones se maneja con JavaScript
        }
        
        // Obtener el ID correcto (producto o su padre si es variación)
        $product_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
        
        if (!$this->product_qualifies_for_discount($product_id)) {
            return $price;
        }
        
        $regular_price = $product->get_regular_price();
        
        if (empty($regular_price)) {
            return $price;
        }
        
        $discount = $this->calculate_discount($regular_price);
        $discounted_price = $regular_price - $discount;
        
        // Asegurar que el descuento no sea mayor que el precio
        if ($discounted_price < 0) {
            $discounted_price = 0;
        }
        
        $price_html = '<del><span class="woocommerce-Price-amount amount">' . wc_price($regular_price) . '</span></del> ';
        $price_html .= '<ins><span class="woocommerce-Price-amount amount">' . wc_price($discounted_price) . '</span></ins>';
        
        return $price_html;
    }
    
    /**
     * Agregar datos de descuento a las variaciones
     */
    public function add_variation_discount_data($variation_data, $product, $variation) {
        // Verificar si el producto padre califica para descuento
        $parent_id = $product->get_id();
        
        if ($this->product_qualifies_for_discount($parent_id)) {
            $options = get_option('woo_auto_discount_options');
            
            // Obtener precio regular de la variación
            $regular_price = $variation->get_regular_price();
            
            if (!empty($regular_price)) {
                $discount = $this->calculate_discount($regular_price);
                $discounted_price = $regular_price - $discount;
                
                // Asegurar que el descuento no sea negativo
                if ($discounted_price < 0) {
                    $discounted_price = 0;
                }
                
                // Agregar datos al objeto de variación
                $variation_data['woo_auto_discount_enabled'] = true;
                $variation_data['woo_auto_discount_original_price'] = $regular_price;
                $variation_data['woo_auto_discount_discounted_price'] = $discounted_price;
                $variation_data['woo_auto_discount_amount'] = $discount;
                $variation_data['woo_auto_discount_type'] = $options['discount_type'];
                $variation_data['woo_auto_discount_value'] = $options['discount_value'];
                
                // Modificar el HTML del precio para mostrar el descuento
                $price_html = '<del><span class="woocommerce-Price-amount amount">' . wc_price($regular_price) . '</span></del> ';
                $price_html .= '<ins><span class="woocommerce-Price-amount amount">' . wc_price($discounted_price) . '</span></ins>';
                
                $variation_data['price_html'] = $price_html;
                $variation_data['display_price'] = $discounted_price;
                $variation_data['display_regular_price'] = $regular_price;
            }
        }
        
        return $variation_data;
    }
    
    /**
     * Agregar JavaScript para actualizar precios de variaciones
     */
    public function add_variation_discount_script() {
        if (!is_product()) {
            return;
        }
        
        global $product;
        
        if (!$product || !$product->is_type('variable')) {
            return;
        }
        
        if (!$this->product_qualifies_for_discount($product->get_id())) {
            return;
        }
        
        $options = get_option('woo_auto_discount_options');
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('🎯 WooCommerce Auto Discount - Soporte para productos variables activado');
            
            var $variationsForm = $('form.variations_form');
            var $priceContainer = $('.woocommerce-variation-price');
            
            // Actualizar precio cuando se selecciona una variación
            $variationsForm.on('found_variation', function(event, variation) {
                if (variation.woo_auto_discount_enabled) {
                    console.log('✅ Descuento aplicado a variación:', {
                        original: variation.woo_auto_discount_original_price,
                        descuento: variation.woo_auto_discount_amount,
                        final: variation.woo_auto_discount_discounted_price
                    });
                    
                    // Agregar animación al contenedor de precio
                    $priceContainer.addClass('price-changed');
                    setTimeout(function() {
                        $priceContainer.removeClass('price-changed');
                    }, 500);
                    
                    // Agregar mensaje informativo si no existe
                    if ($('.woo-auto-discount-variation-message').length === 0) {
                        var discountText = '';
                        if (variation.woo_auto_discount_type === 'percentage') {
                            discountText = variation.woo_auto_discount_value + '%';
                        } else {
                            discountText = '$' + parseFloat(variation.woo_auto_discount_value).toFixed(2);
                        }
                        
                        var message = '<div class="woo-auto-discount-variation-message">' +
                            '<strong>🎉 ¡Descuento aplicado!</strong> ' +
                            'Esta variación tiene un descuento de <strong>' + discountText + '</strong> ' +
                            '(Ahorras: <strong>$' + parseFloat(variation.woo_auto_discount_amount).toFixed(2) + '</strong>)' +
                            '</div>';
                        
                        $priceContainer.after(message);
                    }
                } else {
                    // Remover mensaje si no hay descuento
                    $('.woo-auto-discount-variation-message').fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            });
            
            // Restaurar precio original al limpiar selección
            $variationsForm.on('reset_data', function() {
                console.log('🔄 Variación limpiada');
                $('.woo-auto-discount-variation-message').fadeOut(300, function() {
                    $(this).remove();
                });
            });
            
            // Detectar cuando el usuario cambia de variación
            $variationsForm.on('woocommerce_variation_select_change', function() {
                console.log('🔄 Cambiando variación...');
            });
        });
        </script>
        <?php
    }
}

// Inicializar el plugin
function woo_auto_discount_init() {
    if (class_exists('WooCommerce')) {
        WooAutoDiscount::get_instance();
    } else {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>' . __('WooCommerce Auto Discount requiere que WooCommerce esté instalado y activado.', 'woo-auto-discount') . '</p></div>';
        });
    }
}
add_action('plugins_loaded', 'woo_auto_discount_init');

// Declarar compatibilidad con HPOS
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});


