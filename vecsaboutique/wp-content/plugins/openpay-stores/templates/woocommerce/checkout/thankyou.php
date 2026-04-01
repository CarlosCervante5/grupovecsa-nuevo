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
$images_url = plugin_dir_url(__DIR__) . '../../assets/images/';

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


<!-- Plantilla de referencia de pago -->
<div class="openpay-reference">
    <!-- Encabezado con ícono de éxito -->
    <div class="openpay-reference__header">
        <div class="openpay-reference__icon openpay-reference__icon--success">
            <img src="https://img.openpay.mx/plugins/woocommerce/right.svg" alt="Éxito" class="openpay-reference__icon-image">
        </div>
        <h1 class="openpay-reference__title">Referencia creada con éxito</h1>
        <p class="openpay-reference__subtitle">
            Acude a la tienda aliada más cercana y completa el pago antes de la fecha límite.
        </p>
    </div>

    <!-- Detalles del pago -->
    <div class="openpay-reference__details">
        <!-- Total a pagar -->
        <div class="openpay-reference__detail-row">
            <span class="openpay-reference__detail-label">Total a pagar:</span>
            <span class="openpay-reference__detail-value"><?php echo $order_total; ?>*<?php echo ($country == 'CO' ? ' COP' : ''); ?></span>
        </div>

        <!-- Fecha límite -->
        <div class="openpay-reference__detail-row">
            <span class="openpay-reference__detail-label">Fecha límite:</span>
            <span class="openpay-reference__detail-value"><?php echo $due_date_formatted; ?></span>
        </div>

        <!-- Concepto -->
        <div class="openpay-reference__detail-row">
            <span class="openpay-reference__detail-label">Concepto:</span>
            <span class="openpay-reference__detail-value"><?php echo esc_html($concepto); ?></span>
        </div>

        <!-- Beneficiario -->
        <div class="openpay-reference__detail-row">
            <span class="openpay-reference__detail-label">Beneficiario:</span>
            <span class="openpay-reference__detail-value"><?php echo esc_html($shop_name); ?></span>
        </div>

        <!-- Código de agencia o agente (Solo para Colombia) -->
        <?php if ($country == 'CO') : ?>
            <div class="openpay-reference__detail-row">
                <span class="openpay-reference__detail-label">Código de agencia o agente:</span>
                <div style="text-align: left;">
                    <div class="openpay-reference__detail-value">Efecty:112806</div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Código de agencia o agente (Solo para Perú) -->
        <?php if ($country == 'PE') : ?>
            <div class="openpay-reference__detail-row">
                <span class="openpay-reference__detail-label">Código de agencia o agente:</span>
                <div style="text-align: left;">
                    <div class="openpay-reference__detail-value">BBVA: 11140</div>
                     <div class="openpay-reference__detail-value">BCP: 15813</div>
                      <div class="openpay-reference__detail-value">Interbank: 0791501</div>
                       <div class="openpay-reference__detail-value">KasNet: 220044</div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Nota sobre comisión -->
    <p class="openpay-reference__commission-note">
        * La comisión varía de acuerdo a los términos y condiciones que cada cadena comercial <?php echo ($country == 'CO' ? ' establece' : 'establezca'); ?>
    </p>

    <!-- Sección del código de barras -->
    <div class="openpay-reference__barcode-section">

        <?php if ($country == 'MX') : ?>
            <p class="openpay-reference__instruction">
                Muestra el código impreso o desde tu dispositivo
            </p>

            <?php if ($openpay_barcode_url) : ?>
                <div class="openpay-reference__barcode">
                    <img src="<?php echo esc_url($openpay_barcode_url); ?>"
                         alt="Código de barras"
                         class="openpay-reference__barcode-image">
                </div>
            <?php endif; ?>

            <p class="openpay-reference__instruction openpay-reference__instruction--alt">
                O dicta el número de referencia
            </p>
        <?php elseif ($country == 'CO') : ?>
            <p class="openpay-reference__instruction openpay-reference__instruction--alt">
                Número de referencia
            </p>
        <?php endif; ?>

        <!-- Número de referencia en bloques -->
        <?php if (!empty($reference_parts)) : ?>
            <div class="openpay-reference__number-blocks">
                <?php foreach ($reference_parts as $part) : ?>
                    <span class="openpay-reference__number-block"><?php echo esc_html($part); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Botón de descarga -->
        <?php if ($pdf_url) : ?>
            <a href="<?php echo esc_url($pdf_url); ?>"
               class="openpay-reference__download-btn"
               download>
                <?php echo $country == 'CO' ? 'Imprimir referencia' : 'Descargar referencia'; ?>
            </a>
        <?php endif; ?>
    </div>

    <?php if ($country == 'MX') : ?>
    <!-- Establecimientos donde se puede pagar -->
    <div class="openpay-reference__establishments">
        <p class="openpay-reference__establishments-text">
            Recuerda que puedes realizar tu pago en los siguientes establecimientos:
        </p>

        <div class="openpay-reference__logos">
            <img src="https://img.openpay.mx/plugins/woocommerce/tiendas.png" alt="7-Eleven" class="openpay-reference__logo">
        </div>
    </div>
    <?php endif; ?>

    <?php if ($country == 'PE') : ?>
    <!-- Establecimientos donde se puede pagar -->
    <div class="openpay-reference__establishments">
        <p class="openpay-reference__establishments-text">
            Recuerda que puedes realizar tu pago en los siguientes establecimientos:
        </p>

        <div class="openpay-reference__logos">
            <img src="<?= $images_url ?>establecimientos_pe.png" alt="7-Eleven" class="openpay-reference__logo_pe">
        </div>
    </div>
    <?php endif; ?>

    <!-- Información de contacto -->
    <div class="openpay-reference__contact">
        <?php if ($country == 'MX') : ?>
        <p class="openpay-reference__contact-text">
            ¿Tienes dudas? Puedes contactarnos en
            <a href="mailto:correosoporte@ejemplo.com" class="openpay-reference__contact-link">
                correosoporte@ejemplo.com
            </a>
        </p>
        <?php endif; ?>
        <p class="openpay-reference__footer-text">
            <em>Enviamos esta referencia de pago a tu correo.</em>
        </p>
    </div>


</div>

<style>
    /* ==========================================================================
       Openpay Reference - Componente de referencia de pago
       ========================================================================== */

    /**
     * Contenedor principal del componente
     * Establece el ancho máximo y centrado
     */
    .openpay-reference {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        color: #333;
        background-color: #fff;
    }

    /* Header Section
       ========================================================================== */

    /**
     * Sección del encabezado
     * Contiene el ícono de éxito y títulos
     */
    .openpay-reference__header {
        text-align: center;
        margin-bottom: 30px;
    }

    /**
     * Contenedor del ícono
     * Crea el círculo verde de fondo
     */
    .openpay-reference__icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /**
     * Imagen dentro del ícono
     * Tamaño del check
     */
    .openpay-reference__icon-image {
        width: 45px;
        height: 45px;
    }

    /**
     * Título principal
     */
    .openpay-reference__title {
        font-size: 24px;
        font-weight: 600;
        margin: 0 0 10px 0;
        color: #333;
    }

    /**
     * Subtítulo explicativo
     */
    .openpay-reference__subtitle {
        font-size: 14px;
        color: #666;
        margin: 0;
        line-height: 1.5;
    }

    /* Details Section
       ========================================================================== */

    /**
     * Contenedor de los detalles del pago
     * Agrupa las filas de información
     */
    .openpay-reference__details {
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
    }

    /**
     * Fila individual de detalle
     * Layout con etiqueta a la izquierda y valor a la derecha
     */
    .openpay-reference__detail-row {
        display: flex;
        justify-content: flex-start;
        gap: 10px;
        padding: 12px 0;
        border-bottom: 1px solid #e0e0e0;
    }

    /**
     * Elimina el borde inferior de la última fila
     */
    .openpay-reference__detail-row:last-child {
        border-bottom: none;
    }

    /**
     * Etiqueta del detalle (izquierda)
     */
    .openpay-reference__detail-label {
        font-weight: bold;
        color: #333;
        font-size: 14px;
        flex: 0 0 50%;
    }

    /**
     * Valor del detalle (izquierda)
     * Alineado a la izquierda y con color más claro
     */
    .openpay-reference__detail-value {
        color: #666;
        font-size: 14px;
        text-align: left;
        flex: 0 0 50%;
    }

    /* Commission Note
       ========================================================================== */

    /**
     * Nota sobre la comisión
     * Texto pequeño en gris
     */
    .openpay-reference__commission-note {
        font-size: 12px;
        color: #999;
        margin: 10px 0 25px 0;
        text-align: left;
    }

    /* Barcode Section
       ========================================================================== */

    /**
     * Sección del código de barras
     * Contiene instrucciones, código y número de referencia
     */
    .openpay-reference__barcode-section {
        text-align: center;
        margin-bottom: 30px;
    }

    /**
     * Texto de instrucciones
     */
    .openpay-reference__instruction {
        font-size: 14px;
        color: #333;
        margin: 0 0 15px 0;
        font-weight: bold;
    }

    /**
     * Modificador para instrucción alternativa
     * Añade más espaciado superior
     */
    .openpay-reference__instruction--alt {
        margin-top: 20px;
    }

    /**
     * Contenedor del código de barras
     * Sin borde ni fondo, solo contiene la imagen
     */
    .openpay-reference__barcode {
        margin: 20px 0;
        padding: 0;
        background-color: transparent;
    }

    /**
     * Imagen del código de barras
     * Ajuste automático al contenedor
     */
    .openpay-reference__barcode-image {
        max-width: 100%;
        display: block;
        margin: 0 auto;
        width: 285px;
        height: 75px;
    }

    /* Reference Number Blocks
       ========================================================================== */

    /**
     * Contenedor de los bloques numéricos
     * Distribuye los bloques horizontalmente
     */
    .openpay-reference__number-blocks {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin: 20px 0;
        flex-wrap: wrap;
    }

    /**
     * Bloque individual de número
     * Caja gris con número dentro
     */
    .openpay-reference__number-block {
        background-color: #e0e0e0;
        padding: 12px 20px;
        border-radius: 4px;
        font-size: 18px;
        font-weight: 600;
        color: #333;
        letter-spacing: 2px;
    }

    /* Download Button
       ========================================================================== */

    /**
     * Botón de descarga
     * Botón turquesa/cyan con texto blanco
     */
    .openpay-reference__download-btn {
        display: inline-block;
        background-color: #4db8a8;
        color: #fff;
        padding: 12px 30px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        margin-top: 20px;
        transition: background-color 0.3s ease;
    }

    /**
     * Estado hover del botón
     * Oscurece ligeramente el color de fondo
     */
    .openpay-reference__download-btn:hover {
        background-color: #3da89a;
    }

    /* Establishments Section
       ========================================================================== */

    /**
     * Sección de establecimientos
     * Muestra los logos de tiendas donde se puede pagar
     */
    .openpay-reference__establishments {
        margin-bottom: 25px;
    }

    /**
     * Texto explicativo de establecimientos
     */
    .openpay-reference__establishments-text {
        font-size: 13px;
        color: #666;
        text-align: center;
        margin-bottom: 15px;
    }

    /**
     * Contenedor de logos
     * Grid responsivo para los logos de tiendas
     */
    .openpay-reference__logos {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        padding: 15px 0;
    }

    /**
     * Logo individual de establecimiento
     */
    .openpay-reference__logo {
        height: 35px;
        width: auto;
        object-fit: contain;
    }

    /**
     * Logo individual de establecimiento para Perú
     */
    .openpay-reference__logo_pe {
        width: auto;
        max-width: 130%;
        object-fit: contain;
    }

    /* Contact Section
       ========================================================================== */

    /**
     * Sección de contacto
     * Información de soporte y confirmación
     */
    .openpay-reference__contact {
        text-align: center;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
    }

    /**
     * Texto de contacto
     */
    .openpay-reference__contact-text {
        font-size: 13px;
        color: #666;
        margin: 0 0 10px 0;
    }

    /**
     * Link de correo de contacto
     */
    .openpay-reference__contact-link {
        color: #5cb85c;
        text-decoration: none;
    }

    /**
     * Estado hover del link
     */
    .openpay-reference__contact-link:hover {
        text-decoration: underline;
    }

    /**
     * Texto del footer
     * Mensaje en cursiva sobre el envío del correo
     */
    .openpay-reference__footer-text {
        font-size: 13px;
        color: #999;
        margin: 10px 0 0 0;
    }

    /* Responsive Design
       ========================================================================== */

    /**
     * Adaptaciones para móviles
     */
    @media (max-width: 768px) {
        .openpay-reference {
            padding: 15px;
        }

        .openpay-reference__title {
            font-size: 20px;
        }

        .openpay-reference__subtitle {
            font-size: 13px;
        }

        .openpay-reference__details {
            padding: 15px;
        }

        .openpay-reference__detail-row {
            flex-direction: column;
            gap: 5px;
            padding: 10px 0;
        }

        .openpay-reference__number-block {
            padding: 10px 15px;
            font-size: 16px;
        }

        .openpay-reference__logos {
            gap: 10px;
        }

        .openpay-reference__logo {
            height: 30px;
        }
    }

    /**
     * Adaptaciones para pantallas muy pequeñas
     */
    @media (max-width: 480px) {
        .openpay-reference__number-blocks {
            gap: 8px;
        }

        .openpay-reference__number-block {
            padding: 8px 12px;
            font-size: 14px;
        }
    }
</style>