<?php
namespace OpenpayStores\Services;

use OpenpayStores\Includes\OpenpayClient;
use WC_Order;

/**
 * Procesa los webhooks de Openpay desde la cola de Action Scheduler de WC.
 * Se registra a sí mismo al hook 'openpay_stores_process_webhook'.
 */
class OpenpayWebhookProcessorService
{

    protected $logger;

    public function __construct()
    {
        $this->logger = wc_get_logger();

        // Este log nos dice si la clase se está cargando
        // (es decir, si el `new OpenpayWebhookProcessor()` en el Gateway funciona).
        //$this->logger->info('[WebhookProcessor] Clase cargada y hook "openpay_stores_process_webhook" registrado.');

        // ¡Este es el hook que consume la tarea de la cola!
        // Coincide con el nombre usado en WC()->queue()->add()
        add_action('openpay_stores_process_webhook', array($this, 'handle_webhook'), 10, 1);
    }

    /**
     * El manejador real del webhook. Aquí ocurren las "notificaciones".
     *
     * @param array $args Argumentos pasados desde la cola (contiene el payload).
     */
    public function handle_webhook($args)
    {
        // Si ves este, la cola funciona.
        $this->logger->info('[WebhookProcessor] ¡TAREA RECIBIDA! Procesando args: ' . json_encode($args));

        $payload = $args;

        // Verificamos que el payload no esté vacío y sea un string
        if (empty($payload) || !is_string($payload)) {
            $this->logger->error('[WebhookProcessor] Tarea de cola ejecutada sin payload (o no es un string).');
            return;
        }

        $this->logger->info('[WebhookProcessor] Procesando webhook: ' . $payload);

        $event = json_decode($payload);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($event->type)) {
            $this->logger->error('[WebhookProcessor] Error: Payload de webhook inválido o no es JSON.');
            return;
        }

        if (isset($event->transaction->method) && $event->transaction->method !== 'store') {
            $this->logger->info('[WebhookProcessor] El método no es "store" (' . $event->transaction->method . '). Ignorando webhook.');
            return;
        }

        $order = wc_get_order($event->transaction->order_id);

        if (!$order) {
            $this->logger->error('[WebhookProcessor] Orden no encontrada: ' . $event->transaction->order_id);
            return;
        }

        try {
            // 4. Inicializar API de Openpay (Necesario para consultar el estado real del cargo)
            $openpay = $this->init_openpay_api();
            if (!$openpay) {
                throw new \Exception('No se pudieron cargar las credenciales de Openpay.');
            }

            // 5. Obtener el cargo real desde la API (Lógica antigua recuperada)
            // Esto es vital para asegurar que el estado es real y no solo confiar en el JSON.
            $charge = null;
            if (isset($event->transaction->customer_id) && !empty($event->transaction->customer_id)) {
                $this->logger->info('[WebhookProcessor] Buscando cargo a través del cliente: ' . $event->transaction->customer_id);
                $customer = $openpay->customers->get($event->transaction->customer_id);
                $charge = $customer->charges->get($event->transaction->id);
            } else {
                $this->logger->info('[WebhookProcessor] Buscando cargo directamente.');
                $charge = $openpay->charges->get($event->transaction->id);
            }

            // 6. Procesar según el tipo de evento
            switch ($event->type) {
                case 'charge.succeeded':
                    $this->handle_payment_succeeded($order, $event, $charge);
                    break;

                case 'transaction.expired':
                    $this->handle_payment_expired($order, $event, $charge);
                    break;

                case 'charge.failed':
                case 'charge.cancelled':
                    $this->handle_payment_failed($order, $event, $charge);
                    break;

                default:
                    $this->logger->info('[WebhookProcessor] Evento no manejado: ' . $event->type);
            }

        } catch (\Exception $e) {
            $this->logger->error('[WebhookProcessor] Excepción crítica: ' . $e->getMessage());
        }

    }

    protected function init_openpay_api()
    {
        $settings = get_option('woocommerce_openpay_stores_settings', []);

        if (empty($settings)) {
            return null;
        }

        $is_sandbox = ($settings['sandbox'] ?? 'no') === 'yes';
        $country = $settings['country'] ?? 'MX';
        $merchant_id = $is_sandbox ? ($settings['test_merchant_id'] ?? '') : ($settings['live_merchant_id'] ?? '');
        $private_key = $is_sandbox ? ($settings['test_private_key'] ?? '') : ($settings['live_private_key'] ?? '');

        if (!$merchant_id || !$private_key) {
            return null;
        }

        return OpenpayClient::getInstance($merchant_id, $private_key, $country, $is_sandbox);
    }

    /**
     * Notificación: El pago fue exitoso.
     */
    private function handle_payment_succeeded(WC_Order $order, $event, $charge)
    {
        if ($charge->status !== 'completed') {
            $this->logger->warning('[WebhookProcessor] Discrepancia: Evento succeeded pero cargo status es: ' . $charge->status);
            return;
        }

        if ($order->is_paid()) {
            $this->logger->info('[WebhookProcessor] Orden ' . $order->get_id() . ' ya estaba pagada. Ignorando.');
            return;
        }

        $this->logger->info('[WebhookProcessor] Marcando orden ' . $order->get_id() . ' como pagada.');

        $payment_date = date("Y-m-d", strtotime($event->event_date));
        $order->update_meta_data('openpay_payment_date', $payment_date);

        // Completar pago
        $order->payment_complete($charge->id);
        $order->add_order_note(sprintf(__('Pago en tienda completado. ID Transacción: %s', 'openpay_stores'), $charge->id));

        $this->logger->info('[WebhookProcessor] Orden pagada exitosamente.');
    }

    /**
     * Notificación: La referencia de pago expiró.
     */
    private function handle_payment_expired(WC_Order $order, $event, $charge)
    {
        // Solo procesamos si la orden sigue esperando pago
        if (!$order->has_status('on-hold')) {
            return;
        }

        if ($charge->status === 'cancelled') {
            $order->update_status('cancelled', __('La referencia de pago ha expirado y el cargo fue cancelado.', 'openpay_stores'));
            $this->logger->info('[WebhookProcessor] Orden cancelada por expiración.');
        } else {
            $this->logger->warning('[WebhookProcessor] Evento expirado recibido, pero el status del cargo es: ' . $charge->status);
        }
    }

    /**
     * Notificación: El pago falló o fue cancelado.
     */
    private function handle_payment_failed(WC_Order $order, $event, $charge)
    {
        if ($order->has_status('on-hold') && $charge->status === 'cancelled') {
            $order->update_status('failed', __('El pago fue cancelado o falló.', 'openpay_stores'));
            $this->logger->info('[WebhookProcessor] Orden marcada como fallida.');
        }
    }
}
