<?php

use OpenpayStores\Services\OpenpayChargeService;

/**
 * Clase de Pruebas Unitarias para OpenpayChargeService.
 *
 * @covers \OpenpayStores\Services\OpenpayChargeService
 */
class OpenpayChargeServiceTest extends \WP_UnitTestCase
{
    /**
     * @var \WC_Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mock_order;

    /**
     * @var \OpenpayStores\Services\OpenpayCustomerService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mock_customer_service;

    /**
     * @var \stdClass|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mock_openpay_sdk;

    /**
     * @var \stdClass|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mock_openpay_charges_api;

    /**
     * @var \WC_Logger|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mock_logger;

    /**
     * @var OpenpayChargeService
     */
    private $service;

    /**
     * Configuración inicial para cada prueba.
     */
    public function setUp(): void
    {
        parent::setUp();

        // 1. Mock de WC_Order
        $this->mock_order = $this->createMock(\WC_Order::class);
        $this->mock_order->method('get_total')->willReturn('250.50');
        $this->mock_order->method('get_billing_email')->willReturn('test@customer.com');
        $this->mock_order->method('get_id')->willReturn(123);

        // 2. Mock de OpenpayCustomerService
        $this->mock_customer_service = $this->createMock(\OpenpayStores\Services\OpenpayCustomerService::class);

        // 3. Mock del SDK de Openpay
        // Usamos getMockBuilder para añadir el método 'create' a nuestro mock de stdClass
        //
        $this->mock_openpay_charges_api = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['create'])
            ->getMock();

        $this->mock_openpay_sdk = $this->createMock(\stdClass::class);
        $this->mock_openpay_sdk->charges = $this->mock_openpay_charges_api;

        // 4. Mock del Logger
        $this->mock_logger = $this->createMock(\WC_Logger::class);

        // 5. Instanciar el Servicio bajo prueba
        $this->service = new OpenpayChargeService(
            $this->mock_openpay_sdk,
            $this->mock_order,
            $this->mock_customer_service
        );

        // 6. Inyección de Dependencia (Logger)
        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('logger');
        $property->setAccessible(true);
        $property->setValue($this->service, $this->mock_logger);

        // 7. Simular funciones globales de WC
        tests_add_filter('woocommerce_currency', function ($currency) {
            return 'MXN';
        });
    }

    /**
     * @covers \OpenpayStores\Services\OpenpayChargeService::processOpenpayCharge
     * @covers \OpenpayStores\Services\OpenpayChargeService::collectChargeData
     * @covers \OpenpayStores\Services\OpenpayChargeService::create
     * @covers \OpenpayStores\Services\OpenpayChargeService::saveChargeMetaData
     */
    public function test_processOpenpayCharge_success_as_guest()
    {
        // Estado: Usuario no logueado
        wp_set_current_user(0);

        $mock_charge_response = $this->get_mock_charge_response();
        $payment_settings = $this->get_default_payment_settings();
        $payment_settings['country'] = 'MX'; // Para el PDF URL

        // --- Configuración de Mocks ---
        $this->mock_customer_service
            ->expects($this->once())
            ->method('collectCustomerData')
            ->with($this->mock_order)
            ->willReturn(['name' => 'Guest User']);

        $this->mock_openpay_charges_api
            ->expects($this->once())
            ->method('create')
            ->willReturn($mock_charge_response);

        $this->mock_order
            ->expects($this->exactly(6))
            ->method('update_meta_data')
            ->withConsecutive(
                ['_transaction_id', 'ch_12345'],
                ['_country', 'MX'],
                ['_pdf_url', 'http://test.com/m_123/transaction/ch_12345'],
                ['_openpay_barcode_url', 'http://barcode.url'],
                ['_openpay_reference', '1234567890'],
                ['_due_date', '2025-11-06']
            );

        $this->mock_logger->expects($this->atLeastOnce())->method('info');

        // --- Ejecución ---
        $result = $this->service->processOpenpayCharge($payment_settings);

        // --- Verificación ---
        $this->assertSame($mock_charge_response, $result);
        $this->assertEquals('ch_12345', $this->service->get_transaction_id());
    }

    /**
     * @covers \OpenpayStores\Services\OpenpayChargeService::processOpenpayCharge
     * @covers \OpenpayStores\Services\OpenpayChargeService::collectChargeData
     * @covers \OpenpayStores\Services\OpenpayChargeService::create
     * @covers \OpenpayStores\Services\OpenpayChargeService::saveChargeMetaData
     */
    public function test_processOpenpayCharge_success_as_logged_in_user()
    {
        // Estado: Usuario logueado
        $user_id = $this->factory->user->create();
        wp_set_current_user($user_id);

        update_user_meta($user_id, '_openpay_customer_id', 'cust_abc123');

        $mock_charge_response = $this->get_mock_charge_response();
        $payment_settings = $this->get_default_payment_settings();
        $payment_settings['sandbox'] = false; // Probar la lógica de 'no sandbox'

        $mock_openpay_customer = $this->get_mock_openpay_customer_object();
        $payment_settings['openpay_customer'] = $mock_openpay_customer;

        // --- Configuración de Mocks ---
        $this->mock_customer_service
            ->expects($this->never())
            ->method('collectCustomerData');

        $mock_openpay_customer->charges
            ->expects($this->once())
            ->method('create')
            ->willReturn($mock_charge_response);

        $this->mock_openpay_charges_api
            ->expects($this->never())
            ->method('create');

        $this->mock_order
            ->expects($this->exactly(6))
            ->method('update_meta_data');

        // --- Ejecución ---
        $result = $this->service->processOpenpayCharge($payment_settings);

        // --- Verificación ---
        $this->assertSame($mock_charge_response, $result);
    }

    /**
     * @covers \OpenpayStores\Services\OpenpayChargeService::processOpenpayCharge
     */
    public function test_processOpenpayCharge_failure_on_create()
    {
        wp_set_current_user(0);
        $payment_settings = $this->get_default_payment_settings();

        // --- Configuración de Mocks ---
        $this->mock_openpay_charges_api
            ->expects($this->once())
            ->method('create')
            ->willReturn(false);

        $this->mock_order
            ->expects($this->never())
            ->method('update_meta_data');

        // --- Ejecución ---
        $result = $this->service->processOpenpayCharge($payment_settings);

        // --- Verificación ---
        $this->assertFalse($result);
    }

    /**
     * @covers \OpenpayStores\Services\OpenpayChargeService::create
     */
    public function test_create_throws_exception()
    {
        wp_set_current_user(0);
        $this->expectException(\Exception::class);

        // --- Configuración de Mocks ---
        $this->mock_openpay_charges_api
            ->method('create')
            ->will($this->throwException(new \Exception('API Communication Error')));

        // --- Ejecución ---
        $this->service->create(null, ['amount' => 100]);
    }

    /**
     * @covers \OpenpayStores\Services\OpenpayChargeService::collectChargeData
     */
    public function test_collectChargeData_private_method_guest_with_iva()
    {
        wp_set_current_user(0);

        $payment_settings = $this->get_default_payment_settings();
        $payment_settings['country'] = 'CO'; // País que requiere IVA
        $payment_settings['iva'] = 19;

        // --- Configuración de Mocks ---
        $this->mock_customer_service
            ->method('collectCustomerData')
            ->willReturn(['name' => 'Guest User CO']);

        // Usar Reflection para probar el método privado
        $method = $this->get_accessible_method('collectChargeData');

        // --- Ejecución ---
        $charge_request = $method->invoke($this->service, $payment_settings);

        // --- Verificación ---
        $this->assertArrayHasKey('customer', $charge_request); // <-- CORREGIDO
        $this->assertEquals(['name' => 'Guest User CO'], $charge_request['customer']); // <-- CORREGIDO
        $this->assertArrayHasKey('iva', $charge_request); // <-- CORREGIDO
        $this->assertEquals(19, $charge_request['iva']);
        $this->assertEquals('250.50', $charge_request['amount']);
        $this->assertEquals('mxn', $charge_request['currency']);
        $this->assertArrayHasKey('due_date', $charge_request); // <-- CORREGIDO
    }

    /**
     * @covers \OpenpayStores\Services\OpenpayChargeService::saveChargeMetaData
     */
    public function test_saveChargeMetaData_public_method()
    {
        $mock_charge = $this->get_mock_charge_response();
        $country = 'MX';
        $pdf_url = 'http://pdf.url';
        $barcode_url = 'http://barcode.url';
        $reference = '1234567890';
        $due_date = '2025-11-06';

        // --- Configuración de Mocks ---
        $this->mock_order
            ->expects($this->exactly(6))
            ->method('update_meta_data')
            ->withConsecutive(
                ['_transaction_id', $mock_charge->id],
                ['_country', $country],
                ['_pdf_url', $pdf_url],
                ['_openpay_barcode_url', $barcode_url],
                ['_openpay_reference', $reference],
                ['_due_date', $due_date]
            );

        // --- Ejecución ---
        $this->service->saveChargeMetaData($mock_charge, $country, $pdf_url, $barcode_url, $reference, $due_date);
    }


    // --- Métodos de Ayuda (Helpers) ---

    private function get_mock_charge_response()
    {
        $charge = new \stdClass();
        $charge->id = 'ch_12345';
        $charge->due_date = '2025-11-06';

        $charge->payment_method = new \stdClass();
        $charge->payment_method->reference = '1234567890';
        $charge->payment_method->barcode_url = 'http://barcode.url';

        return $charge;
    }

    private function get_mock_openpay_customer_object()
    {
        // También debemos añadir el método 'create' a este mock
        //
        $mock_customer_charges = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['create'])
            ->getMock();

        $mock_openpay_customer = $this->createMock(\stdClass::class);
        $mock_openpay_customer->charges = $mock_customer_charges;
        return $mock_openpay_customer;
    }

    private function get_default_payment_settings()
    {
        // Devolvemos un array, porque esto es lo que usa `processOpenpayCharge`
        return [
            'country' => 'MX',
            'sandbox' => true,
            'deadline' => 24,
            'pdf_url_base' => 'http://test.com',
            'merchant_id' => 'm_123',
            'openpay_customer' => null,
            'iva' => 0
        ];
    }

    private function get_accessible_method($method_name)
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod($method_name);
        $method->setAccessible(true);
        return $method;
    }
}