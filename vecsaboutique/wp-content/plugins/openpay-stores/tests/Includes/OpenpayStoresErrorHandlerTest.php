<?php

use OpenpayStores\Includes\OpenpayStoresErrorHandler;
use Openpay\Data\OpenpayApiTransactionError;
use Openpay\Data\OpenpayApiConnectionError;

/**
 * Pruebas unitarias para OpenpayStoresErrorHandler.
 *
 * @covers \OpenpayStores\Includes\OpenpayStoresErrorHandler
 */
class OpenpayStoresErrorHandlerTest extends \WP_UnitTestCase
{
    /**
     * @var \WC_Logger|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mock_logger;

    /**
     * Configuración para cada prueba: Inyecta un mock en la propiedad estática $logger.
     */
    public function setUp(): void
    {
        parent::setUp();

        // 1. Crear el mock del Logger
        $this->mock_logger = $this->createMock(\WC_Logger::class);

        // 2. Usar Reflection para "secuestrar" la propiedad estática
        $reflection = new \ReflectionClass(OpenpayStoresErrorHandler::class);
        $property = $reflection->getProperty('logger');
        $property->setAccessible(true);

        // 3. Inyectar nuestro mock (null para propiedades estáticas)
        $property->setValue(null, $this->mock_logger);
    }

    // Limpieza después de cada test para no afectar a otros
    public function tearDown(): void
    {
        parent::tearDown();
        $reflection = new \ReflectionClass(OpenpayStoresErrorHandler::class);
        $property = $reflection->getProperty('logger');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    /**
     * Prueba el "happy path" de catchOpenpayStoreError.
     * @covers \OpenpayStores\Includes\OpenpayStoresErrorHandler::catchOpenpayStoreError
     */
    public function test_catchOpenpayStoreError_returns_callback_result_on_success()
    {
        $callback = function () {
            return 'success_value';
        };

        $result = OpenpayStoresErrorHandler::catchOpenpayStoreError($callback);
        $this->assertEquals('success_value', $result);
    }

    /**
     * Prueba que OpenpayApiTransactionError sea capturado y relanzado como una \Exception.
     * @covers \OpenpayStores\Includes\OpenpayStoresErrorHandler::catchOpenpayStoreError
     */
    public function test_catchOpenpayStoreError_handles_OpenpayApiTransactionError()
    {
        // Usar instancia real en lugar de mock
        // El constructor de Exception suele ser (mensaje, código)
        $real_exception = new OpenpayApiTransactionError('Error de transacción', 1003);

        $callback = function () use ($real_exception) {
            throw $real_exception;
        };

        // 2. Verificación de la Excepción
        // Esperamos que se lance una *nueva* \Exception
        $this->expectException(\Exception::class);

        // Esperamos el mensaje de 'clientError' para el código 1003
        $this->expectExceptionMessage('Petición con parámetros incorrectos.');
        $this->expectExceptionCode(1003);

        // 3. Ejecución
        OpenpayStoresErrorHandler::catchOpenpayStoreError($callback);
    }

    /**
     * Prueba que OpenpayApiConnectionError sea capturado y relanzado.
     * @covers \OpenpayStores\Includes\OpenpayStoresErrorHandler::catchOpenpayStoreError
     */
    public function test_catchOpenpayStoreError_handles_OpenpayApiConnectionError()
    {
        // Usar instancia real en lugar de mock
        // El constructor de Exception suele ser (mensaje, código)
        $real_exception = new OpenpayApiConnectionError('Error de conexión', 1004);

        $callback = function () use ($real_exception) {
            throw $real_exception;
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Servicio no disponible.'); // Mensaje de 1004
        $this->expectExceptionCode(1004);

        OpenpayStoresErrorHandler::catchOpenpayStoreError($callback);
    }

    /**
     * Prueba la lógica de logging de handleOpenpayStorePluginException para un error de Openpay.
     * (NOTA: No podemos probar la parte de wc_get_order sin un entorno de integración).
     * @covers \OpenpayStores\Includes\OpenpayStoresErrorHandler::handleOpenpayStorePluginException
     */
    public function test_handleOpenpayStorePluginException_logs_openpay_error()
    {
        // 1. Preparación
        $real_exception = new \Openpay\Data\OpenpayApiError('Error de API', 1005);

        // 2. Callback
        $callback = function () use ($real_exception) {
            throw $real_exception;
        };

        // 3. Aserciones
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Servicio no disponible.'); // Mensaje de 1005
        $this->expectExceptionCode(1005);

        // 4. Ejecución
        OpenpayStoresErrorHandler::catchOpenpayStoreError($callback);
    }

    /**
     * Prueba la lógica de logging de handleOpenpayStorePluginException para una excepción genérica.
     * @covers \OpenpayStores\Includes\OpenpayStoresErrorHandler::handleOpenpayStorePluginException
     */
    public function test_handleOpenpayStorePluginException_logs_generic_exception()
    {
        // 1. Preparación: Crear una excepción genérica
        $generic_exception = new \Exception('Este es un error genérico', 500);

        // 2. Verificación del Logger
        // Esperamos que el logger se llame con el mensaje de la excepción
        $this->mock_logger->expects($this->once())
            ->method('error')
            ->with(
                '[EXCEPTION] Este es un error genérico',
                $this->isType('array')
            );

        // 3. Ejecución
        OpenpayStoresErrorHandler::handleOpenpayStorePluginException($generic_exception);
    }

    /**
     * Prueba la función de logging directamente.
     * @covers \OpenpayStores\Includes\OpenpayStoresErrorHandler::log
     */
    public function test_log_calls_logger_error()
    {
        $this->mock_logger->expects($this->once())
            ->method('error')
            ->with('Mensaje de prueba', ['clave' => 'valor']);

        OpenpayStoresErrorHandler::log('Mensaje de prueba', ['clave' => 'valor']);
    }

    /**
     * Prueba el generador de UUID.
     * @covers \OpenpayStores\Includes\OpenpayStoresErrorHandler::generate_uuid_v4
     */
    public function test_generate_uuid_v4_returns_valid_uuid()
    {
        $uuid = OpenpayStoresErrorHandler::generate_uuid_v4();

        $this->assertIsString($uuid);
        // Patrón Regex para UUID v4
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
        $this->assertMatchesRegularExpression($pattern, $uuid);
    }
}