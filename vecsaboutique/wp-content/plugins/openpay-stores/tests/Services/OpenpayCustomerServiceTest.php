<?php
namespace OpenpayStores\Tests\Services;

use WP_UnitTestCase;
use OpenpayStores\Services\OpenpayCustomerService;
use WC_Logger;

class OpenpayCustomerServiceTest extends WP_UnitTestCase
{
    private $service;
    private $loggerMock;
    private $openpayMock;
    private $orderMock;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock logger
        $this->loggerMock = $this->createMock(WC_Logger::class);

        $customersMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get','add'])
            ->getMock();
        $customersMock->method('get')->willReturn((object)['id' => 'cust_123']);
        $customersMock->method('add')->willReturn((object)['id' => 'cust_new']);

        $this->openpayMock = new \stdClass();
        $this->openpayMock->customers = $customersMock;

        $this->orderMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods([  'get_billing_first_name',
                            'get_billing_last_name',
                            'get_billing_email',
                            'get_billing_phone',
                            'get_billing_address_1',
                            'get_billing_address_2',
                            'get_billing_state',
                            'get_billing_city',
                            'get_billing_postcode',
                            'get_billing_country'])
            ->getMock();
        $this->orderMock->method('get_billing_first_name')->willReturn('John');
        $this->orderMock->method('get_billing_last_name')->willReturn('Doe');
        $this->orderMock->method('get_billing_email')->willReturn('john@example.com');
        $this->orderMock->method('get_billing_phone')->willReturn('5551234567');
        $this->orderMock->method('get_billing_address_1')->willReturn('Street 123');
        $this->orderMock->method('get_billing_address_2')->willReturn('Apt 4');
        $this->orderMock->method('get_billing_state')->willReturn('Querétaro');
        $this->orderMock->method('get_billing_city')->willReturn('Querétaro');
        $this->orderMock->method('get_billing_postcode')->willReturn('76000');
        $this->orderMock->method('get_billing_country')->willReturn('MX');

        // Instancia del servicio con mocks
        $this->service = $this->getMockBuilder(OpenpayCustomerService::class)
            ->setConstructorArgs([$this->openpayMock, 'MX', true])
            ->onlyMethods(['getCustomerId', 'updateCustomerId'])
            ->getMock();

        $this->service->logger = $this->loggerMock;
    }

    public function testRetrieveCustomerWithNoExistingCustomerId()
    {
        wp_set_current_user(1);
        $this->service = new OpenpayCustomerService($this->openpayMock, 'MX', true);
        $result = $this->service->retrieveCustomer($this->orderMock);
        $this->assertEquals((object)['id' => 'cust_new'], $result);
    }

    public function testRetrieveCustomerWithExistingCustomerId()
    {
        wp_set_current_user(1);
        $this->service->method('getCustomerId')->willReturn('cust_123');
        $result = $this->service->retrieveCustomer($this->orderMock);
        $this->assertEquals((object)['id' => 'cust_123'], $result);
    }

    public function testHasAddressReturnsTrue()
    {
        $this->assertTrue($this->service->hasAddress($this->orderMock));
    }

    public function testHasAddressReturnsFalse()
    {
        $order = $this->createMock(\stdClass::class);
        $order = $this->getMockBuilder(\stdClass::class)
            ->addMethods([  'get_billing_address_1',
                'get_billing_state',
                'get_billing_postcode',
                'get_billing_country',
                'get_billing_city',])
            ->getMock();

        $order->method('get_billing_address_1')->willReturn(null);
        $order->method('get_billing_state')->willReturn(null);
        $order->method('get_billing_postcode')->willReturn(null);
        $order->method('get_billing_country')->willReturn(null);
        $order->method('get_billing_city')->willReturn(null);

        $this->assertFalse($this->service->hasAddress($order));
    }

    public function testCollectCustomerDataWithAddressColombia()
    {

        $this->service = new OpenpayCustomerService($this->openpayMock, 'CO', true);
        $data = $this->service->collectCustomerData($this->orderMock);

        $this->assertArrayHasKey('customer_address', $data);
        $this->assertEquals('John', $data['name']);
        $this->assertEquals('Doe', $data['last_name']);
    }

    public function testCreateCustomer()
    {
        //$this->service->method('updateCustomerId')->willReturn(null);
        $this->service = new OpenpayCustomerService($this->openpayMock, 'MX', true);
        $result = $this->service->create($this->orderMock);

        $this->assertEquals('cust_new', $result->id);
    }

    public function testGetCustomerIdReturnsNullWhenNotLoggedIn()
    {
        // Simular que no hay usuario logueado

        wp_set_current_user(0);
        $this->service = new OpenpayCustomerService($this->openpayMock, 'MX', true);
        $result = $this->service->getCustomerId();
        $this->assertEquals('', $result);
    }

    public function testGetCustomerIdReturnsNullWhenLoggedIn()
    {
        // Simular que no hay usuario logueado

        wp_set_current_user(1);
        $this->service = new OpenpayCustomerService($this->openpayMock, 'MX', true);
        $result = $this->service->getCustomerId();
        $this->assertEquals('', $result);
    }

    public function testGetCustomerIdReturnsNullWhenLoggedInProduction()
    {
        // Simular que no hay usuario logueado

        wp_set_current_user(1);
        $this->service = new OpenpayCustomerService($this->openpayMock, 'MX', false);
        $result = $this->service->getCustomerId();
        $this->assertEquals('', $result);
    }


    public function testUpdateCustomerIdWhenNotLoggedIn()
    {
        // Simular que no hay usuario logueado

        wp_set_current_user(0);
        $this->service = new OpenpayCustomerService($this->openpayMock, 'MX', true);
        $result = $this->service->updateCustomerId('cus_id');

        $this->assertNull($result);
    }

    public function testUpdateCustomerIdWhenLoggedIn()
    {
        // Simular que no hay usuario logueado

        wp_set_current_user(1);
        $this->service = new OpenpayCustomerService($this->openpayMock, 'MX', true);
        $result = $this->service->updateCustomerId('cus_id');

        $this->assertNull($result);
    }

    public function testUpdateCustomerIdWhenLoggedInProduction()
    {
        // Simular que no hay usuario logueado

        wp_set_current_user(1);
        $this->service = new OpenpayCustomerService($this->openpayMock, 'MX', false);
        $result = $this->service->updateCustomerId('cus_id');

        $this->assertNull($result);
    }

}