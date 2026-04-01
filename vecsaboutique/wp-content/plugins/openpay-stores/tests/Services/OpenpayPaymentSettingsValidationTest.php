<?php
namespace OpenpayStores\Tests\Services;

use OpenpayStores\Services\PaymentSettings\OpenpayPaymentSettingsValidation;
use WP_UnitTestCase;
use WC_Logger;
use Mockery;

class OpenpayPaymentSettingsValidationTest extends WP_UnitTestCase
{

    private $mock_logger;
    private $gateway_id = 'openpay_stores';

    public function setUp(): void
    {
        parent::setUp();
        // Creamos un "doble" del logger de WooCommerce para cada prueba.
        $this->mock_logger = $this->createMock(WC_Logger::class);
    }

    /**
     * Limpia Mockery después de cada prueba para evitar conflictos.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_validate_credentials_returns_api_instance_on_success()
    {
        // ARRANGE (Preparar)

        // 1. Creamos un mock para el objeto anidado 'webhooks'.
        // Le enseñamos a responder al método 'getList'.
        $mock_webhooks = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getList'])
            ->getMock();
        $mock_webhooks->method('getList')->willReturn(['un_webhook']);

        // 2. Creamos el mock principal para OpenpayApi (del tipo correcto).
        $mock_openpay_api = $this->createMock(\Openpay\Data\OpenpayApi::class);

        // 3. Configuramos el método mágico __get.
        // "Cuando alguien intente obtener la propiedad 'webhooks'..."
        $mock_openpay_api->method('__get')
            ->with($this->equalTo('webhooks'))
            ->willReturn($mock_webhooks); // "...devuelve nuestro mock de webhooks."

        // 4. Creamos el "mock parcial" de nuestra clase de validación.
        $validator = $this->getMockBuilder(OpenpayPaymentSettingsValidation::class)
            ->setConstructorArgs([$this->mock_logger, $this->gateway_id])
            ->onlyMethods(['createOpenpayApiInstance'])
            ->getMock();

        // 5. Le decimos que devuelva el mock de OpenpayApi que acabamos de configurar.
        $validator->method('createOpenpayApiInstance')
            ->willReturn($mock_openpay_api);

        // 6. Preparamos los datos de configuración simulados.
        $settings_validos = [
            'woocommerce_openpay_stores_sandbox' => '1',
            'woocommerce_openpay_stores_test_merchant_id' => 'id_valido',
            'woocommerce_openpay_stores_test_private_key' => 'sk_valido'
        ];

        // ACT (Actuar)
        // Cuando se ejecute $openpay->webhooks, se activará nuestro mock de __get.
        $result = $validator->validateOpenpayCredentials($settings_validos);

        // ASSERT (Verificar)
        // La aserción ahora pasará porque el objeto devuelto sí es del tipo OpenpayApi.
        $this->assertInstanceOf(\Openpay\Data\OpenpayApi::class, $result);
    }

    /**
     * Prueba el caso de fallo: las credenciales están vacías.
     */
    public function test_validate_credentials_returns_null_when_credentials_are_empty()
    {
        // ARRANGE
        $validator = new OpenpayPaymentSettingsValidation($this->mock_logger, $this->gateway_id);
        $settings_invalidos = [
            'woocommerce_openpay_stores_sandbox' => '1',
            'woocommerce_openpay_stores_test_merchant_id' => '',
            'woocommerce_openpay_stores_test_private_key' => ''
        ];

        // ACT
        $result = $validator->validateOpenpayCredentials($settings_invalidos);

        // ASSERT
        $this->assertNull($result);
    }

    /**
     * Prueba el bloque CATCH: Simula un fallo en la llamada a la API de Openpay.
     */
    public function test_validate_credentials_returns_null_on_api_exception()
    {
        // ARRANGE
        // Mockeamos la API para que lance una excepción al llamar a getList
        $mock_webhooks = $this->getMockBuilder(\stdClass::class)->addMethods(['getList'])->getMock();
        $mock_webhooks->method('getList')->willThrowException(new \Exception('Error de conexión simulado'));

        $mock_openpay_api = $this->createMock(\Openpay\Data\OpenpayApi::class);
        $mock_openpay_api->method('__get')->with('webhooks')->willReturn($mock_webhooks);

        // Mock parcial del validador
        $validator = $this->getMockBuilder(OpenpayPaymentSettingsValidation::class)
            ->setConstructorArgs([$this->mock_logger, $this->gateway_id])
            ->onlyMethods(['createOpenpayApiInstance'])
            ->getMock();

        $validator->method('createOpenpayApiInstance')->willReturn($mock_openpay_api);

        $settings = [
            'woocommerce_openpay_stores_sandbox' => '1',
            'woocommerce_openpay_stores_test_merchant_id' => 'id',
            'woocommerce_openpay_stores_test_private_key' => 'sk'
        ];

        // Expectativa: El logger debe registrar el error
        $this->mock_logger->expects($this->atLeastOnce())
            ->method('error')
            ->with($this->stringContains('Fallo en la validación de API'));

        // ACT
        $result = $validator->validateOpenpayCredentials($settings);

        // ASSERT
        $this->assertNull($result);
    }

    /**
     * Prueba la selección de credenciales de PRODUCCIÓN (Sandbox desactivado).
     * Verifica que lea las llaves _live_ en lugar de _test_.
     */
    public function test_validate_credentials_uses_live_keys_when_sandbox_disabled()
    {
        // ARRANGE
        // Mock básico para que pase la validación de API
        $mock_webhooks = $this->getMockBuilder(\stdClass::class)->addMethods(['getList'])->getMock();
        $mock_webhooks->method('getList')->willReturn([]);
        $mock_openpay_api = $this->createMock(\Openpay\Data\OpenpayApi::class);
        $mock_openpay_api->method('__get')->with('webhooks')->willReturn($mock_webhooks);

        // Mock parcial: Esta vez verificamos los argumentos que recibe createOpenpayApiInstance
        $validator = $this->getMockBuilder(OpenpayPaymentSettingsValidation::class)
            ->setConstructorArgs([$this->mock_logger, $this->gateway_id])
            ->onlyMethods(['createOpenpayApiInstance'])
            ->getMock();

        // Expectativa: Debe llamarse con las credenciales LIVE
        $validator->expects($this->once())
            ->method('createOpenpayApiInstance')
            ->with(
                'live_id_123',
                'live_sk_456',
                'MX',
                false
            )
            ->willReturn($mock_openpay_api);

        $settings = [
            // Sandbox NO está presente o es 0
            'woocommerce_openpay_stores_country' => 'MX',
            'woocommerce_openpay_stores_live_merchant_id' => 'live_id_123',
            'woocommerce_openpay_stores_live_private_key' => 'live_sk_456',
            // Las de test deben ser ignoradas
            'woocommerce_openpay_stores_test_merchant_id' => 'test_id_ignorado'
        ];

        // ACT
        $validator->validateOpenpayCredentials($settings);
    }

    /**
     * Prueba validateCurrency: Caso de Éxito.
     */
    public function test_validate_currency_returns_true_for_valid_currency()
    {
        // ARRANGE
        $validator = new OpenpayPaymentSettingsValidation($this->mock_logger, $this->gateway_id);

        // Usar el filtro de WooCommerce si está disponible en tu entorno de test
        add_filter('woocommerce_currency', function () {
            return 'MXN';
        });

        $allowed_currencies = ['MXN', 'USD'];

        // ACT
        $result = $validator->validateCurrency($allowed_currencies);

        // ASSERT
        $this->assertTrue($result);
    }

    /**
     * Prueba validateCurrency: Caso de Error (Moneda no soportada).
     */
    public function test_validate_currency_returns_false_for_invalid_currency()
    {
        // ARRANGE
        $validator = new OpenpayPaymentSettingsValidation($this->mock_logger, $this->gateway_id);

        // Cambiamos la moneda a una no permitida (ej. EUR)
        add_filter('woocommerce_currency', function () {
            return 'EUR';
        });

        $allowed_currencies = ['MXN', 'USD'];

        // ACT
        $result = $validator->validateCurrency($allowed_currencies);

        // ASSERT
        $this->assertFalse($result);
    }

    /**
     * Prueba el método protegido createOpenpayApiInstance.
     * Verifica que realmente llame a OpenpayClient y retorne una instancia de OpenpayApi.
     */
    public function test_createOpenpayApiInstance_returns_valid_instance()
    {
        // ARRANGE
        // Usamos la instancia REAL de la clase, no un Mock, porque queremos probar el código real del método.
        $validator = new OpenpayPaymentSettingsValidation($this->mock_logger, $this->gateway_id);

        // Usamos Reflection para hacer accesible el método protegido
        $method = new \ReflectionMethod(OpenpayPaymentSettingsValidation::class, 'createOpenpayApiInstance');
        $method->setAccessible(true);

        // ACT
        // Invocamos el método con datos de prueba
        $result = $method->invoke(
            $validator,
            'merchant_id_dummy',
            'private_key_dummy',
            'MX',
            true // is_sandbox
        );

        // ASSERT
        // Verificamos que el resultado sea una instancia real de la API de Openpay
        $this->assertInstanceOf(\Openpay\Data\OpenpayApi::class, $result);
    }
}