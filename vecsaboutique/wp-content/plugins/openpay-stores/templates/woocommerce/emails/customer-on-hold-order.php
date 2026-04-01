<?php
/**
 * Plantilla de email para pagos con Openpay Stores
 *
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener datos del pedido
$order_id = $order->get_id();
$customer_name = $order->get_billing_first_name();
$shop_name = get_bloginfo('name');
$order_total = $order->get_formatted_order_total();

// Obtener datos de Openpay del pedido (ajusta según tu implementación)
$pdf_url = $order->get_meta('_pdf_url');
$openpay_barcode_url = $order->get_meta('_openpay_barcode_url');
$openpay_reference = $order->get_meta('_openpay_reference');
$openpay_due_date = $order->get_meta('_due_date');
$country= $order->get_meta('_country');

// Formatear fecha límite
$due_date_formatted = '';
if ($openpay_due_date) {
    // Crear objeto DateTime con la zona horaria incluida en la fecha
    $date = new DateTime($openpay_due_date);
    // Formatear la fecha en español manteniendo la zona horaria original del objeto
    $due_date_formatted = wp_date('j \d\e F \a \l\a\s H:i \h', $date->getTimestamp(), $date->getTimezone());
}

// Obtener productos del pedido
$items = $order->get_items();
$product_names = array();
foreach ($items as $item) {
    $product_names[] = $item->get_name();
}
$concepto = implode(', ', $product_names);

// Dividir referencia en grupos de 4 dígitos
$reference_parts = array();
if ($openpay_reference) {
    $reference_parts = str_split($openpay_reference, 4);
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html__('Referencia de Pago - Openpay', 'openpay-stores'); ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center" style="padding: 20px 0;">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; max-width: 600px;">
                <!-- Header -->
                <tr>
                    <td style="background-color: #002b5c; padding: 30px; text-align: center;">
                        <?php
                        $header_image = get_option('woocommerce_email_header_image');
                        if ($header_image) {
                            echo '<img src="' . esc_url($header_image) . '" alt="' . esc_attr($shop_name) . '" width="180" style="display: block; margin: 0 auto;">';
                        } else {
                            echo '<img src="https://img.openpay.mx/plugins/woocommerce/openpay_aqua.png" alt="Openpay by BBVA" width="180" style="display: block; margin: 0 auto;">';
                        }
                        ?>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding: 40px 30px;">
                        <!-- Greeting -->
                        <h1 style="color: #333333; font-size: 24px; margin: 0 0 20px 0; text-align: center;">
                            <?php echo sprintf(esc_html__('Hola %s', 'openpay-stores'), esc_html($customer_name)); ?>
                        </h1>

                        <!-- Description -->
                        <p style="color: #666666; font-size: 14px; line-height: 1.6; margin: 0 0 30px 0;">
                            <?php echo sprintf(
                                    esc_html__('Te compartimos la referencia de pago en efectivo de tu compra en %s. Realiza tu pago antes de la fecha límite.', 'openpay-stores'),
                                    '<strong>' . esc_html($shop_name) . '</strong>'
                            ); ?>
                        </p>

                        <!-- Payment Summary Title -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
                            <tr>
                                <td style="padding-bottom: 15px;">
                                        <span style="color: #333333; font-size: 16px;">
                                            <?php echo esc_html__('Resumen del pago:', 'openpay-stores'); ?>
                                        </span>
                                    <?php
                                        if($country == 'CO'){
                                            echo '<img src="https://img.openpay.mx/plugins/woocommerce/efecty.png" alt="Efecty" width="80" style="float: right;">';
                                        }
                                        if($country == 'MX'){
                                            echo '<img src="https://img.openpay.mx/plugins/woocommerce/paynet.png" alt="Paynet" width="80" style="float: right;">';
                                        }
                                    ?>

                                </td>
                            </tr>
                        </table>

                        <!-- Payment Details -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 30px; padding-left: 45px; padding-right: 45px; ">
                            <tr>
                                <td style="padding: 12px 0; border-bottom: 1px solid #eeeeee;">
                                        <span style="color: #333333; font-size: 14px; font-weight: bold;">
                                            <?php echo esc_html__('Total a pagar:', 'openpay-stores'); ?>
                                        </span>
                                </td>
                                <td style="padding: 12px 0; border-bottom: 1px solid #eeeeee; text-align: left;">
                                        <span style="color: #333333; font-size: 14px;">
                                            <?php echo wp_kses_post($order_total); ?>* <?php echo ($country == 'CO' ? ' COP' : ''); ?>
                                        </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 0; border-bottom: 1px solid #eeeeee;">
                                        <span style="color: #333333; font-size: 14px; font-weight: bold;">
                                            <?php echo esc_html__('Fecha límite de pago:', 'openpay-stores'); ?>
                                        </span>
                                </td>
                                <td style="padding: 12px 0; border-bottom: 1px solid #eeeeee; text-align: left;">
                                        <span style="color: #333333; font-size: 14px;">
                                            <?php echo esc_html($due_date_formatted); ?>
                                        </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 0; border-bottom: 1px solid #eeeeee;">
                                        <span style="color: #333333; font-size: 14px; font-weight: bold;">
                                            <?php echo esc_html__('Concepto:', 'openpay-stores'); ?>
                                        </span>
                                </td>
                                <td style="padding: 12px 0; border-bottom: 1px solid #eeeeee; text-align: left;">
                                        <span style="color: #333333; font-size: 14px;">
                                            <?php echo esc_html($concepto); ?>
                                        </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 0; border-bottom: 1px solid #eeeeee;">
                                        <span style="color: #333333; font-size: 14px; font-weight: bold;">
                                            <?php echo esc_html__('Beneficiario:', 'openpay-stores'); ?>
                                        </span>
                                </td>
                                <td style="padding: 12px 0; border-bottom: 1px solid #eeeeee; text-align: left;">
                                        <span style="color: #333333; font-size: 14px;">
                                            <?php echo esc_html($shop_name); ?>
                                        </span>
                                </td>
                            </tr>
                            <?php if ($country == 'CO') : ?>
                            <tr>
                                <td style="padding: 12px 0; border-bottom: 1px solid #eeeeee;">
                                        <span style="color: #333333; font-size: 14px; font-weight: bold;">
                                            <?php echo esc_html__('Número de convenio:', 'openpay-stores'); ?>
                                        </span>
                                </td>
                                <td style="padding: 12px 0; border-bottom: 1px solid #eeeeee; text-align: left;">
                                    <div>
                                    <span style="color: #333333; font-size: 14px;">
                                        <strong>Efecty:</strong> 112806
                                    </span>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($country == 'PE') : ?>
                            <tr>
                                <td style="padding: 12px 0; border-bottom: 1px solid #eeeeee;">
                                        <span style="color: #333333; font-size: 14px; font-weight: bold;">
                                            <?php echo esc_html__('Código de agencia o agente:', 'openpay-stores'); ?>
                                        </span>
                                </td>
                                <td style="padding: 12px 0; border-bottom: 1px solid #eeeeee; text-align: left;">
                                    <div>
                                    <span style="color: #333333; font-size: 14px;">
                                        <strong>BBVA:</strong> 11140
                                    </span>
                                    </div>
                                    <div>
                                    <span style="color: #333333; font-size: 14px;">
                                        <strong>BCP:</strong> 15813
                                    </span>
                                    </div>
                                    <div>
                                    <span style="color: #333333; font-size: 14px;">
                                        <strong>Interbank:</strong> 0791501
                                    </span>
                                    </div>
                                    <div>
                                    <span style="color: #333333; font-size: 14px;">
                                        <strong>Kasnet:</strong> 220044
                                    </span>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>

                        <!-- Commission Note -->
                        <p style="color: #999999; font-size: 12px; margin: 0 0 30px 0;">
                            <?php echo esc_html__('* La comisión varía de acuerdo a los términos y condiciones que cada cadena comercial establece.', 'openpay-stores'); ?>
                        </p>

                        <!-- Barcode Section -->
                        <?php if ($openpay_barcode_url): ?>
                            <div style="text-align: center; margin-bottom: 30px;">
                                <?php if ($country == 'MX') : ?>
                                    <p style="color: #333333; font-size: 14px; font-weight: bold; margin-bottom: 15px;">
                                        <?php echo esc_html__('Muestra el código impreso o desde tu dispositivo', 'openpay-stores'); ?>
                                    </p>
                                    <img src="<?php echo esc_url($openpay_barcode_url); ?>" alt="<?php echo esc_attr__('Código de barras', 'openpay-stores'); ?>" width="300" height="80" style="display: block; margin: 0 auto 15px auto; height: 80px; width: 300px;">
                                <?php endif; ?>
                                <?php if ($country == 'CO') : ?>
                                    <p style="color: #333333; font-size: 15px; font-weight: bold; margin-bottom: 15px;">
                                        <?php echo esc_html__('Número de referencia', 'openpay-stores'); ?>
                                    </p>
                                <?php endif; ?>
                                <?php endif; ?>

                        <!-- Reference Number -->
                        <?php if (!empty($reference_parts)): ?>
                            <div style="text-align: center; margin-bottom: 30px;">
                                <?php if ($country == 'MX') : ?>
                                    <p style="color: #333333; font-size: 14px; font-weight: bold; margin-bottom: 15px;">
                                        <?php echo esc_html__('O dicta el número de referencia', 'openpay-stores'); ?>
                                    </p>
                                <?php endif; ?>
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
                                    <tr>
                                        <?php foreach ($reference_parts as $index => $part): ?>
                                            <td style="background-color: #dddddd; padding: 15px 20px; margin: 0 5px; font-size: 18px; font-weight: bold; color: #333333; border-radius: 5px;">
                                                <?php echo esc_html($part); ?>
                                            </td>
                                            <?php if ($index < count($reference_parts) - 1): ?>
                                                <td style="width: 10px;"></td>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tr>
                                </table>
                            </div>
                        <?php endif; ?>

                        <?php if ($country == 'MX') : ?>
                            <!-- Payment Locations -->
                            <p style="color: #333333; font-size: 14px; margin-bottom: 15px;">
                                <?php echo esc_html__('Recuerda que puedes realizar tu pago en los siguientes establecimientos:', 'openpay-stores'); ?>
                            </p>
                            <table role="presentation" width="100%" cellpadding="10" cellspacing="0" border="0" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center" width="100%">
                                        <img src="https://img.openpay.mx/plugins/woocommerce/tiendas.png" alt="tiendas" style="display: block;">
                                    </td>
                                </tr>
                            </table>
                        <?php endif; ?>

                         <?php if ($country == 'PE') : ?>
                            <!-- Payment Locations -->
                            <p style="color: #333333; font-size: 14px; margin-bottom: 15px;">
                                <?php echo esc_html__('Recuerda que puedes realizar tu pago en los siguientes establecimientos:', 'openpay-stores'); ?>
                            </p>
                            <table role="presentation" width="100%" cellpadding="10" cellspacing="0" border="0" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center" width="100%">
                                        <img src="https://img.openpay.mx/plugins/woocommerce/establecimientos_pe.png" alt="tiendas" style="display: block;">
                                    </td>
                                </tr>
                            </table>
                        <?php endif; ?>


                        <!-- Print Button -->
                        <?php if ($pdf_url): ?>
                            <div style="text-align: center; margin-bottom: 30px;">
                                <a href="<?php echo esc_url($pdf_url); ?>" style="display: inline-block; background-color: #4db8a8; color: #ffffff; text-decoration: none; padding: 11px 38px; font-size: 16px; border-radius: 5px; font-weight: bold;">
                                    <?php echo esc_html__('Imprimir referencia', 'openpay-stores'); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Social Media -->
                        <div style="text-align: center; margin-bottom: 20px;">
                            <p style="color: #333333; font-size: 14px; margin-bottom: 15px; font-weight: bold;">
                                <?php echo esc_html__('Seguimos conectados', 'openpay-stores'); ?>
                            </p>
                            <table role="presentation" cellpadding="5" cellspacing="0" border="0" style="margin: 0 auto;">
                                <tr>
                                    <td>
                                        <a href="https://www.linkedin.com/company/openpay" style="display: inline-block;">
                                            <img src="https://img.openpay.mx/plugins/woocommerce/linkedin.png" alt="LinkedIn" width="30" height="30" style="border-radius: 50%;">
                                        </a>
                                    </td>
                                    <td>
                                        <a href="https://www.facebook.com/openpay.mx" style="display: inline-block;">
                                            <img src="https://img.openpay.mx/plugins/woocommerce/facebook.png" alt="Facebook" width="30" height="30" style="border-radius: 50%;">
                                        </a>
                                    </td>
                                    <td>
                                        <a href="https://www.instagram.com/openpay_mx" style="display: inline-block;">
                                            <img src="https://img.openpay.mx/plugins/woocommerce/instagram.png" alt="Instagram" width="30" height="30" style="border-radius: 50%;">
                                        </a>
                                    </td>
                                    <td>
                                        <a href="https://www.youtube.com/openpay" style="display: inline-block;">
                                            <img src="https://img.openpay.mx/plugins/woocommerce/web_page.png" alt="YouTube" width="30" height="30" style="border-radius: 50%;">
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background-color: #002b5c; padding: 30px; text-align: center;">
                        <img src="https://img.openpay.mx/plugins/woocommerce/we_are_open.png" alt="We are Open" width="150" style="display: block; margin: 0 auto 15px auto;">
                        <p style="color: #ffffff; font-size: 12px; margin: 0;">
                            <?php echo sprintf(
                                    esc_html__('© %s Openpay. Todos los derechos reservados.', 'openpay-stores'),
                                    date('Y')
                            ); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>