<?php
/**
 * Template para la página de configuración del admin
 */

if (!defined('ABSPATH')) {
    exit;
}

$options = get_option('woo_auto_discount_options');

// Valores por defecto si no existen
$defaults = array(
    'enabled' => 'no',
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

$options = wp_parse_args($options, $defaults);

// Obtener categorías de productos
$product_categories = get_terms(array(
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
));

// Obtener etiquetas de productos
$product_tags = get_terms(array(
    'taxonomy' => 'product_tag',
    'hide_empty' => false,
));
?>

<div class="wrap woo-auto-discount-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors(); ?>
    
    <form method="post" action="options.php">
        <?php settings_fields('woo_auto_discount_options'); ?>
        
        <div class="woo-auto-discount-container">
            
            <!-- Configuración General -->
            <div class="woo-auto-discount-card">
                <h2>⚙️ Configuración General</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enabled"><?php _e('Habilitar Descuentos', 'woo-auto-discount'); ?></label>
                        </th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="woo_auto_discount_options[enabled]" id="enabled" <?php checked($options['enabled'], 'yes'); ?>>
                                <span class="slider"></span>
                            </label>
                            <p class="description"><?php _e('Activar o desactivar los descuentos automáticos', 'woo-auto-discount'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Configuración de Descuentos -->
            <div class="woo-auto-discount-card">
                <h2>💰 Configuración de Descuentos</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="discount_type"><?php _e('Tipo de Descuento', 'woo-auto-discount'); ?></label>
                        </th>
                        <td>
                            <select name="woo_auto_discount_options[discount_type]" id="discount_type">
                                <option value="percentage" <?php selected($options['discount_type'], 'percentage'); ?>><?php _e('Porcentaje (%)', 'woo-auto-discount'); ?></option>
                                <option value="fixed" <?php selected($options['discount_type'], 'fixed'); ?>><?php _e('Monto Fijo ($)', 'woo-auto-discount'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="discount_value"><?php _e('Valor del Descuento', 'woo-auto-discount'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="woo_auto_discount_options[discount_value]" id="discount_value" value="<?php echo esc_attr($options['discount_value']); ?>" step="0.01" min="0" class="regular-text">
                            <p class="description"><?php _e('Ingresa el valor del descuento (ej: 10 para 10% o $10)', 'woo-auto-discount'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Aplicar a -->
            <div class="woo-auto-discount-card">
                <h2>🎯 Aplicar Descuento a</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="apply_to"><?php _e('Aplicar por', 'woo-auto-discount'); ?></label>
                        </th>
                        <td>
                            <select name="woo_auto_discount_options[apply_to]" id="apply_to">
                                <option value="category" <?php selected($options['apply_to'], 'category'); ?>><?php _e('Categoría', 'woo-auto-discount'); ?></option>
                                <option value="tag" <?php selected($options['apply_to'], 'tag'); ?>><?php _e('Etiqueta', 'woo-auto-discount'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr class="apply-category" style="<?php echo $options['apply_to'] === 'category' ? '' : 'display:none;'; ?>">
                        <th scope="row">
                            <label for="selected_categories"><?php _e('Seleccionar Categorías', 'woo-auto-discount'); ?></label>
                        </th>
                        <td>
                            <select name="woo_auto_discount_options[selected_categories][]" id="selected_categories" multiple class="woo-auto-discount-multiselect" size="10">
                                <?php if (empty($product_categories)) : ?>
                                    <option disabled><?php _e('No hay categorías disponibles', 'woo-auto-discount'); ?></option>
                                <?php else : ?>
                                    <?php foreach ($product_categories as $category) : ?>
                                        <option value="<?php echo esc_attr($category->term_id); ?>" <?php echo in_array($category->term_id, $options['selected_categories']) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?> productos)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <p class="description">
                                <strong><?php _e('💡 Consejo:', 'woo-auto-discount'); ?></strong>
                                <?php _e('Mantén presionado Ctrl (Windows) o Cmd (Mac) para seleccionar múltiples categorías', 'woo-auto-discount'); ?><br>
                                <span style="color: #2271b1;">
                                    <?php 
                                    if (!empty($options['selected_categories'])) {
                                        echo sprintf(__('✓ %d categoría(s) seleccionada(s)', 'woo-auto-discount'), count($options['selected_categories']));
                                    } else {
                                        echo __('⚠️ Ninguna categoría seleccionada', 'woo-auto-discount');
                                    }
                                    ?>
                                </span>
                            </p>
                        </td>
                    </tr>
                    
                    <tr class="apply-tag" style="<?php echo $options['apply_to'] === 'tag' ? '' : 'display:none;'; ?>">
                        <th scope="row">
                            <label for="selected_tags"><?php _e('Seleccionar Etiquetas', 'woo-auto-discount'); ?></label>
                        </th>
                        <td>
                            <select name="woo_auto_discount_options[selected_tags][]" id="selected_tags" multiple class="woo-auto-discount-multiselect" size="10">
                                <?php if (empty($product_tags)) : ?>
                                    <option disabled><?php _e('No hay etiquetas disponibles. Crea algunas etiquetas primero.', 'woo-auto-discount'); ?></option>
                                <?php else : ?>
                                    <?php foreach ($product_tags as $tag) : ?>
                                        <option value="<?php echo esc_attr($tag->term_id); ?>" <?php echo in_array($tag->term_id, $options['selected_tags']) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($tag->name); ?> (<?php echo $tag->count; ?> productos)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <p class="description">
                                <strong><?php _e('💡 Consejo:', 'woo-auto-discount'); ?></strong>
                                <?php _e('Mantén presionado Ctrl (Windows) o Cmd (Mac) para seleccionar múltiples etiquetas', 'woo-auto-discount'); ?><br>
                                <span style="color: #2271b1;">
                                    <?php 
                                    if (!empty($options['selected_tags'])) {
                                        echo sprintf(__('✓ %d etiqueta(s) seleccionada(s)', 'woo-auto-discount'), count($options['selected_tags']));
                                    } else {
                                        echo __('⚠️ Ninguna etiqueta seleccionada', 'woo-auto-discount');
                                    }
                                    ?>
                                </span>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Configuración de Badge -->
            <div class="woo-auto-discount-card">
                <h2>🏷️ Configuración del Badge</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="show_badge"><?php _e('Mostrar Badge', 'woo-auto-discount'); ?></label>
                        </th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="woo_auto_discount_options[show_badge]" id="show_badge" <?php checked($options['show_badge'], 'yes'); ?>>
                                <span class="slider"></span>
                            </label>
                            <p class="description"><?php _e('Mostrar badge de oferta en productos con descuento', 'woo-auto-discount'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="badge_text"><?php _e('Texto del Badge', 'woo-auto-discount'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="woo_auto_discount_options[badge_text]" id="badge_text" value="<?php echo esc_attr($options['badge_text']); ?>" class="regular-text">
                            <p class="description"><?php _e('Texto que aparecerá en el badge (ej: ¡OFERTA!, SALE, DESCUENTO)', 'woo-auto-discount'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="show_discount_amount"><?php _e('Mostrar Monto de Descuento', 'woo-auto-discount'); ?></label>
                        </th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="woo_auto_discount_options[show_discount_amount]" id="show_discount_amount" <?php checked($options['show_discount_amount'], 'yes'); ?>>
                                <span class="slider"></span>
                            </label>
                            <p class="description"><?php _e('Mostrar el porcentaje o monto de descuento en el badge', 'woo-auto-discount'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="badge_style"><?php _e('Estilo del Badge', 'woo-auto-discount'); ?></label>
                        </th>
                        <td>
                            <select name="woo_auto_discount_options[badge_style]" id="badge_style">
                                <option value="modern" <?php selected($options['badge_style'], 'modern'); ?>><?php _e('Moderno', 'woo-auto-discount'); ?></option>
                                <option value="classic" <?php selected($options['badge_style'], 'classic'); ?>><?php _e('Clásico', 'woo-auto-discount'); ?></option>
                                <option value="minimal" <?php selected($options['badge_style'], 'minimal'); ?>><?php _e('Minimalista', 'woo-auto-discount'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="badge_color"><?php _e('Color del Badge', 'woo-auto-discount'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="woo_auto_discount_options[badge_color]" id="badge_color" value="<?php echo esc_attr($options['badge_color']); ?>" class="color-picker">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="badge_text_color"><?php _e('Color del Texto', 'woo-auto-discount'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="woo_auto_discount_options[badge_text_color]" id="badge_text_color" value="<?php echo esc_attr($options['badge_text_color']); ?>" class="color-picker">
                        </td>
                    </tr>
                </table>
                
                <div class="badge-preview">
                    <h3><?php _e('Vista Previa del Badge', 'woo-auto-discount'); ?></h3>
                    <div class="preview-container">
                        <span class="woo-auto-discount-badge woo-auto-discount-badge-<?php echo esc_attr($options['badge_style']); ?>" id="badge-preview" style="background-color: <?php echo esc_attr($options['badge_color']); ?>; color: <?php echo esc_attr($options['badge_text_color']); ?>;">
                            <?php echo esc_html($options['badge_text']); ?>
                            <?php if ($options['show_discount_amount'] === 'yes') : ?>
                                <?php if ($options['discount_type'] === 'percentage') : ?>
                                    -<?php echo esc_html($options['discount_value']); ?>%
                                <?php else : ?>
                                    -$<?php echo number_format($options['discount_value'], 2); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Resumen antes de guardar -->
        <div class="woo-auto-discount-card" style="background: #f0f6fc; border-left: 4px solid #2271b1;">
            <h2>📋 Resumen de Configuración</h2>
            <table class="form-table">
                <tr>
                    <th><?php _e('Estado:', 'woo-auto-discount'); ?></th>
                    <td>
                        <strong style="color: <?php echo $options['enabled'] === 'yes' ? '#00a32a' : '#d63638'; ?>;">
                            <?php echo $options['enabled'] === 'yes' ? '✅ Activo' : '❌ Inactivo'; ?>
                        </strong>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Descuento:', 'woo-auto-discount'); ?></th>
                    <td>
                        <?php 
                        if ($options['discount_type'] === 'percentage') {
                            echo '<strong>' . esc_html($options['discount_value']) . '%</strong>';
                        } else {
                            echo '<strong>$' . number_format($options['discount_value'], 2) . '</strong>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Aplicar a:', 'woo-auto-discount'); ?></th>
                    <td>
                        <?php 
                        if ($options['apply_to'] === 'category') {
                            $count = count($options['selected_categories']);
                            echo sprintf(__('<strong>%d Categoría(s)</strong>', 'woo-auto-discount'), $count);
                        } else {
                            $count = count($options['selected_tags']);
                            echo sprintf(__('<strong>%d Etiqueta(s)</strong>', 'woo-auto-discount'), $count);
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Badge:', 'woo-auto-discount'); ?></th>
                    <td>
                        <?php echo $options['show_badge'] === 'yes' ? '✅ Visible' : '❌ Oculto'; ?>
                    </td>
                </tr>
            </table>
            
            <?php if ($options['enabled'] === 'yes' && (($options['apply_to'] === 'category' && empty($options['selected_categories'])) || ($options['apply_to'] === 'tag' && empty($options['selected_tags'])))) : ?>
                <div class="warning-box">
                    <strong>⚠️ Advertencia:</strong> Los descuentos están activos pero no has seleccionado ninguna categoría o etiqueta. No se aplicará a ningún producto.
                </div>
            <?php endif; ?>
        </div>
        
        <?php submit_button(__('💾 Guardar Cambios', 'woo-auto-discount'), 'primary', 'submit', true, array('style' => 'margin-top: 20px;')); ?>
    </form>
</div>

