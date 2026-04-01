<?php

use OpenpayStores\Services\OpenpayWebhookProcessorService;

class OpenpayWebhookProcessorServiceTest extends \WP_UnitTestCase
{
    /** @var TestableOpenpayWebhookProcessorService */
    private $processor_mocked;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $mock_logger;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $mock_openpay_api;

    /** @var int */
    private $order_id;

    /** @var WC_Order */
    private $order;

    public function setUp(): void
    {
        parent::setUp();

        // 1. Crear Orden
        $this->order = \WC_Helper_Order::create_order();
        $this->order_id = $this->order->get_id();
        $this->order->set_status('on-hold');
        $this->order->save();

        // 2. Mocks
        $this->mock_logger = $this->createMock(\WC_Logger::class);

        $this->mock_openpay_api = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['customers', 'charges'])
            ->getMock();

        $this->mock_openpay_api->customers = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();

        $this->mock_openpay_api->charges = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();

        // 3. Instancia Mockeada (Para probar lógica de negocio - Imágenes 1, 2, 3)
        $this->processor_mocked = new TestableOpenpayWebhookProcessorService();
        $this->processor_mocked->setMockApi($this->mock_openpay_api);

        // Inyectar Logger
        $reflection = new \ReflectionClass($this->processor_mocked);
        $property = $reflection->getProperty('logger');
        $property->setAccessible(true);
        $property->setValue($this->processor_mocked, $this->mock_logger);
    }

    /**
     * Helper para crear un payload JSON válido para las pruebas.
     *
     * @param string $type El tipo de evento (ej. 'charge.succeeded')
     * @param string|null $customer_id ID del cliente (opcional)
     * @return string JSON encodeado
     */
    private function create_valid_payload($type, $customer_id = null)
    {
        return json_encode([
            'type' => $type,
            'event_date' => '2025-11-12T12:00:00',
            'transaction' => [
                'method' => 'store',
                'id' => 'tr_123',
                'order_id' => $this->order_id,
                'customer_id' => $customer_id
            ]
        ]);
    }

    public function test_handle_webhook_empty_or_invalid_payload()
    {
        // Caso 1: Array (no string)
        $this->mock_logger->expects($this->atLeastOnce())
            ->method('error')
            ->with($this->stringContains('no es un string'));
        $this->processor_mocked->handle_webhook(['dato' => 'erroneo']);

        // Caso 2: Vacío
        $this->processor_mocked->handle_webhook('');
    }

    public function test_handle_webhook_invalid_json_format()
    {
        // Enviamos un string que no es JSON válido
        $invalid_json = '{"type": "charge.succeeded"';

        $this->mock_logger->expects($this->atLeastOnce())
            ->method('error')
            ->with($this->stringContains('no es JSON')); // Usamos una parte única del mensaje

        $this->processor_mocked->handle_webhook($invalid_json);
    }

    public function test_handle_webhook_wrong_payment_method()
    {
        $payload = json_encode([
            'type' => 'charge.succeeded',
            'transaction' => [
                'method' => 'card', // Método incorrecto
                'id' => 'tr_123',
                'order_id' => $this->order_id
            ]
        ]);

        // Capturamos todos los logs en un array
        // Esto evita el error de que PHPUnit evalúe el log incorrecto ("TAREA RECIBIDA")
        $captured_logs = [];
        $this->mock_logger->method('info')->will($this->returnCallback(function ($message) use (&$captured_logs) {
            $captured_logs[] = $message;
            return true;
        }));

        $this->processor_mocked->handle_webhook($payload);

        // Buscamos manualmente el mensaje en los logs capturados
        $found = false;
        foreach ($captured_logs as $log) {
            if (strpos($log, 'El método no es "store"') !== false) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'No se encontró el log esperado: "El método no es "store""');
    }

    public function test_handle_webhook_order_not_found()
    {
        // 1. Preparar Payload con un ID de orden inexistente (ej. 99999)
        $payload = json_encode([
            'type' => 'charge.succeeded',
            'transaction' => [
                'method' => 'store',
                'id' => 'tr_123',
                'order_id' => 99999 // <-- ID que NO existe en la BD de pruebas
            ]
        ]);

        // 2. Expectativa: El logger debe recibir el mensaje de error específico
        $this->mock_logger->expects($this->atLeastOnce())
            ->method('error')
            ->with($this->stringContains('Orden no encontrada'));

        // 3. Ejecutar
        // Esto llamará a wc_get_order(99999), que devolverá false, activando tu IF
        $this->processor_mocked->handle_webhook($payload);
    }

    public function test_handle_webhook_api_init_failure()
    {
        // 1. Payload válido para pasar las validaciones iniciales
        $payload = json_encode([
            'type' => 'charge.succeeded',
            'transaction' => [
                'method' => 'store',
                'id' => 'tr_123',
                'order_id' => $this->order_id
            ]
        ]);

        // 2. FORZAR que init_openpay_api devuelva NULL
        // Esto hará que el código entre en el if (!$openpay)
        $this->processor_mocked->setMockApi(null);

        // 3. Expectativa: El catch debe capturar la excepción lanzada
        // El mensaje del log será: "[WebhookProcessor] Excepción crítica: No se pudieron cargar las credenciales de Openpay."
        $this->mock_logger->expects($this->atLeastOnce())
            ->method('error')
            ->with($this->stringContains('Excepción crítica'));

        // 4. Ejecutar
        $this->processor_mocked->handle_webhook($payload);
    }

    public function test_handle_webhook_customer_lookup()
    {
        // Payload con customer_id
        $payload = $this->create_payload('charge.succeeded', $this->order_id, 'cust_123');

        $mock_customer = $this->getMockBuilder(\stdClass::class)->addMethods(['get'])->getMock();
        $mock_charge = (object) ['id' => 'tr_123', 'status' => 'completed'];

        // Esperamos que llame a customers->get y luego charge
        $this->mock_openpay_api->customers->expects($this->once())->method('get')->with('cust_123')->willReturn($mock_customer);
        $mock_customer->charges = $this->getMockBuilder(\stdClass::class)->addMethods(['get'])->getMock();
        $mock_customer->charges->expects($this->once())->method('get')->willReturn($mock_charge);

        $this->processor_mocked->handle_webhook($payload);

        // Verificar que la orden se procesó
        $updated_order = wc_get_order($this->order_id);
        $this->assertEquals('processing', $updated_order->get_status());
    }

    public function test_handle_webhook_direct_lookup()
    {
        $payload = $this->create_payload('charge.succeeded', $this->order_id, null); // Sin customer_id

        $mock_charge = (object) ['id' => 'tr_123', 'status' => 'completed'];
        $this->mock_openpay_api->charges->expects($this->once())->method('get')->willReturn($mock_charge);

        $this->processor_mocked->handle_webhook($payload);
    }

    public function test_handle_webhook_critical_exception()
    {
        $payload = $this->create_payload('charge.succeeded', $this->order_id);

        // Hacemos que la API lance una excepción
        $this->mock_openpay_api->charges->method('get')->will($this->throwException(new \Exception('Error API')));

        $this->mock_logger->expects($this->atLeastOnce())
            ->method('error')
            ->with($this->stringContains('Excepción crítica'));

        $this->processor_mocked->handle_webhook($payload);
    }

    public function test_handle_payment_expired_not_on_hold()
    {
        $this->order->set_status('processing'); // Ya no está on-hold
        $this->order->save();

        $payload = $this->create_payload('transaction.expired', $this->order_id);

        // Mockeamos respuesta API
        $mock_charge = (object) ['id' => 'tr_123', 'status' => 'cancelled'];
        $this->mock_openpay_api->charges->method('get')->willReturn($mock_charge);

        // No debe cambiar el estado
        $this->processor_mocked->handle_webhook($payload);
        $this->assertEquals('processing', wc_get_order($this->order_id)->get_status());
    }

    public function test_handle_payment_expired_success()
    {
        $payload = $this->create_payload('transaction.expired', $this->order_id);

        $mock_charge = (object) ['id' => 'tr_123', 'status' => 'cancelled'];
        $this->mock_openpay_api->charges->method('get')->willReturn($mock_charge);

        $this->processor_mocked->handle_webhook($payload);
        $this->assertEquals('cancelled', wc_get_order($this->order_id)->get_status());
    }

    public function test_handle_payment_expired_mismatch()
    {
        $payload = $this->create_payload('transaction.expired', $this->order_id);

        // El webhook dice expired, pero la API dice 'in_progress'
        $mock_charge = (object) ['id' => 'tr_123', 'status' => 'in_progress'];
        $this->mock_openpay_api->charges->method('get')->willReturn($mock_charge);

        $this->mock_logger->expects($this->atLeastOnce())
            ->method('warning')
            ->with($this->stringContains('Evento expirado recibido, pero el status'));

        $this->processor_mocked->handle_webhook($payload);
        $this->assertEquals('on-hold', wc_get_order($this->order_id)->get_status());
    }

    public function test_handle_charge_failed()
    {
        $payload_failed = $this->create_payload('charge.failed', $this->order_id);
        $payload_cancelled = $this->create_payload('charge.cancelled', $this->order_id);

        $mock_charge = (object) ['id' => 'tr_123', 'status' => 'cancelled'];
        $this->mock_openpay_api->charges->method('get')->willReturn($mock_charge);

        $this->processor_mocked->handle_webhook($payload_failed);
        $this->processor_mocked->handle_webhook($payload_cancelled);
        $this->assertEquals('failed', wc_get_order($this->order_id)->get_status());

    }

    public function test_handle_succeeded_status_mismatch()
    {
        // 1. Payload de éxito
        $payload = $this->create_valid_payload('charge.succeeded');

        // 2. Mock de API: Devuelve un estado DIFERENTE a 'completed'
        $mock_charge = (object) ['id' => 'tr_123', 'status' => 'in_progress'];

        $this->mock_openpay_api->charges->method('get')->willReturn($mock_charge);

        // 3. Expectativa: Se debe registrar una advertencia (warning)
        $this->mock_logger->expects($this->atLeastOnce())
            ->method('warning')
            ->with($this->stringContains('Discrepancia'));

        // 4. Ejecutar
        $this->processor_mocked->handle_webhook($payload);

        // 5. Verificación extra: El estado de la orden NO debe haber cambiado
        $updated_order = wc_get_order($this->order_id);
        $this->assertEquals('on-hold', $updated_order->get_status());
    }

    public function test_handle_succeeded_already_paid()
    {
        // 1. Preparación: Marcar la orden como PAGADA en la base de datos
        $this->order->set_status('completed');
        $this->order->save();

        // 2. Payload y Mock correctos (API confirma que está pagado)
        $payload = $this->create_valid_payload('charge.succeeded');
        $mock_charge = (object) ['id' => 'tr_123', 'status' => 'completed'];
        $this->mock_openpay_api->charges->method('get')->willReturn($mock_charge);

        // 3. Captura de Logs para buscar el mensaje específico
        $captured_logs = [];
        $this->mock_logger->method('info')->will($this->returnCallback(function ($message) use (&$captured_logs) {
            $captured_logs[] = $message;
            return true;
        }));

        // 4. Ejecutar
        $this->processor_mocked->handle_webhook($payload);

        // 5. Verificación: Buscamos el mensaje de "ya estaba pagada"
        $found = false;
        foreach ($captured_logs as $log) {
            if (strpos($log, 'ya estaba pagada') !== false) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'No se encontró el log esperado: "ya estaba pagada"');
    }

    public function test_init_api_returns_null_when_no_settings()
    {
        delete_option('woocommerce_openpay_stores_settings');

        $realService = new OpenpayWebhookProcessorService();
        $method = new \ReflectionMethod(OpenpayWebhookProcessorService::class, 'init_openpay_api');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($realService));
    }


    public function test_init_api_returns_null_when_credentials_missing()
    {
        $settings = ['sandbox' => 'yes', 'test_merchant_id' => '', 'test_private_key' => ''];
        update_option('woocommerce_openpay_stores_settings', $settings);

        $realService = new OpenpayWebhookProcessorService();
        $method = new \ReflectionMethod(OpenpayWebhookProcessorService::class, 'init_openpay_api');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($realService));
    }


    public function test_init_api_success_sandbox()
    {
        $settings = ['sandbox' => 'yes', 'country' => 'MX', 'test_merchant_id' => 'id', 'test_private_key' => 'sk'];
        update_option('woocommerce_openpay_stores_settings', $settings);

        $realService = new OpenpayWebhookProcessorService();
        $method = new \ReflectionMethod(OpenpayWebhookProcessorService::class, 'init_openpay_api');
        $method->setAccessible(true);

        $this->assertInstanceOf(\Openpay\Data\OpenpayApi::class, $method->invoke($realService));
    }


    public function test_init_api_success_live()
    {
        $settings = ['sandbox' => 'no', 'country' => 'CO', 'live_merchant_id' => 'id', 'live_private_key' => 'sk'];
        update_option('woocommerce_openpay_stores_settings', $settings);

        $realService = new OpenpayWebhookProcessorService();
        $method = new \ReflectionMethod(OpenpayWebhookProcessorService::class, 'init_openpay_api');
        $method->setAccessible(true);

        $this->assertInstanceOf(\Openpay\Data\OpenpayApi::class, $method->invoke($realService));
    }


    private function create_payload($type, $order_id, $customer_id = null)
    {
        return json_encode([
            'type' => $type,
            'event_date' => '2025-11-12T12:00:00',
            'transaction' => [
                'method' => 'store',
                'id' => 'tr_123',
                'order_id' => $order_id,
                'customer_id' => $customer_id
            ]
        ]);
    }
}

/**
 * Subclase Testable
 */
class TestableOpenpayWebhookProcessorService extends OpenpayWebhookProcessorService
{
    private $mock_api_instance;

    public function setMockApi($mock)
    {
        $this->mock_api_instance = $mock;
    }

    protected function init_openpay_api()
    {
        return $this->mock_api_instance;
    }
}