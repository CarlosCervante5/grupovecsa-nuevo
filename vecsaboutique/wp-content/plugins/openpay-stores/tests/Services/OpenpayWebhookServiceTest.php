<?php

namespace OpenpayStores\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use OpenpayStores\Services\OpenpayWebhookService;
use Openpay\Data\OpenpayApi;
use Openpay\Resources\OpenpayWebhook;
use WC_Logger;

/**
 * Prueba unitaria para el OpenpayWebhookService.
 * * Usamos 'extends TestCase' de PHPUnit para un test unitario PURO,
 * ya que no necesitamos cargar WordPress.
 */
class OpenpayWebhookServiceTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|OpenpayApi */
    private $mockOpenpayApi;

    /** @var \PHPUnit\Framework\MockObject\MockObject|WC_Logger */
    private $mockLogger;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $mockWebhookResource; // Este será el mock de $openpay->webhooks

    /**
     * Esta función se ejecuta ANTES de cada test.
     * Prepara nuestros mocks.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Creamos los mocks de las dependencias
        $this->mockOpenpayApi = $this->createMock(OpenpayApi::class);
        $this->mockLogger = $this->createMock(WC_Logger::class);

        // 2. Mockear el objeto $openpay->webhooks
        // El SDK de Openpay usa propiedades mágicas (ej. $openpay->webhooks).
        // Necesitamos simular un objeto que tenga los métodos getList(), add(), get(), delete().
        $this->mockWebhookResource = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getList', 'add', 'get', 'delete'])
            ->getMock();

        // 3. Le decimos al mock de OpenpayApi que, cuando alguien acceda
        //    a su propiedad 'webhooks', devuelva nuestro mock del recurso.
        $this->mockOpenpayApi->method('__get')
            ->with($this->equalTo('webhooks'))
            ->willReturn($this->mockWebhookResource);
    }

    /**
     * Prueba el escenario donde no hay webhooks y se debe crear uno.
     */
    public function test_reconcileWebhooks_creates_new_webhook_if_none_exist()
    {
        // 1. ARRANGE (Preparar)

        // A. Configuramos los mocks
        // "Cuando el servicio llame a getList(), devuelve un array vacío."
        $this->mockWebhookResource->method('getList')
            ->willReturn([]);

        // B. Configuramos el mock del webhook que será DEVUELTO
        // El SDK de Openpay usa métodos mágicos, así que simulamos __get
        $mockNewWebhook = $this->createMock(OpenpayWebhook::class);
        $mockNewWebhook->method('__get')
            ->with($this->equalTo('id')) // Cuando alguien pida la propiedad 'id'
            ->willReturn('wh_test_12345'); // Devuelve nuestro ID de prueba

        // "Esperamos que el método 'add' sea llamado EXACTAMENTE UNA VEZ"
        $this->mockWebhookResource->expects($this->once())
            ->method('add') // Cuando se llame a 'add'
            ->willReturn($mockNewWebhook); // Devuelve nuestro webhook simulado.

        // C. Definimos las URLs de prueba
        $target_url = 'https://sitioprueba.com/wc-api/Openpay_Stores';
        $target_url_simple = 'https://sitioprueba.com/index.php?wc-api=Openpay_Stores';

        // D. Instanciamos nuestro servicio (el Sujeto de Pruebas)
        $service = new OpenpayWebhookService($this->mockOpenpayApi, $this->mockLogger);

        // 2. ACT (Actuar)
        // Llamamos al método que queremos probar
        $result = $service->reconcileWebhooks($target_url, $target_url_simple);

        // 3. ASSERT (Verificar)
        // Verificamos que el resultado es el que esperamos
        $this->assertEquals('created', $result['status']);
        $this->assertEquals('wh_test_12345', $result['id']); // Esta línea ahora debería funcionar
    }

    /**
     * Prueba el escenario donde se encuentra un webhook obsoleto (mismo path,
     * diferente dominio) y se debe eliminar y crear uno nuevo.
     */
    public function test_reconcileWebhooks_deletes_obsolete_and_creates_new()
    {
        // 1. ARRANGE (Preparar)

        // A. Definimos las URLs de prueba
        $target_url_pretty = 'https://sitionuevo.com/wc-api/Openpay_Stores';
        $target_url_simple = 'https://sitionuevo.com/index.php?wc-api=Openpay_Stores';

        // Esta es la URL "antigua" que simularemos que Openpay devuelve
        $obsolete_url = 'https://sitioantiguo.com/wc-api/Openpay_Stores';

        // B. Configuramos el mock del webhook "obsoleto"
        $mockObsoleteWebhook = $this->createMock(OpenpayWebhook::class);

        // Simulamos el acceso a sus propiedades 'id' y 'url'
        $mockObsoleteWebhook->method('__get')
            ->willReturnMap([
                ['id', 'wh_obsoleto_5678'],
                ['url', $obsolete_url]
            ]);

        // C. Configuramos el mock del webhook "nuevo" que se creará
        $mockNewWebhook = $this->createMock(OpenpayWebhook::class);
        $mockNewWebhook->method('__get')
            ->with($this->equalTo('id'))
            ->willReturn('wh_nuevo_1234');

        // D. Configuramos el mock del recurso $openpay->webhooks

        // "Cuando se llame a getList(), devuelve una lista con el webhook obsoleto"
        $this->mockWebhookResource->method('getList')
            ->willReturn([$mockObsoleteWebhook]);

        // "Esperamos que el método 'get' sea llamado UNA VEZ con el ID 'wh_obsoleto_5678'"
        // Esto es necesario para la función deleteWebhook()
        $this->mockWebhookResource->expects($this->once())
            ->method('get')
            ->with($this->equalTo('wh_obsoleto_5678'))
            ->willReturn($mockObsoleteWebhook); // Devuelve el objeto para que se pueda llamar a ->delete()

        // "Esperamos que el método 'delete' del *objeto obsoleto* sea llamado UNA VEZ"
        $mockObsoleteWebhook->expects($this->once())
            ->method('delete');

        // "Esperamos que el método 'add' (para crear el nuevo) sea llamado UNA VEZ"
        $this->mockWebhookResource->expects($this->once())
            ->method('add')
            ->willReturn($mockNewWebhook);

        // E. Instanciamos nuestro servicio
        $service = new OpenpayWebhookService($this->mockOpenpayApi, $this->mockLogger);

        // 2. ACT (Actuar)
        $result = $service->reconcileWebhooks($target_url_pretty, $target_url_simple);

        // 3. ASSERT (Verificar)
        // Verificamos que el resultado es el que esperamos
        $this->assertEquals('created', $result['status']);
        $this->assertEquals('wh_nuevo_1234', $result['id']);
    }

    /**
     * Prueba el escenario donde el webhook correcto ya existe.
     * No debe borrar nada y no debe crear nada.
     */
    public function test_reconcileWebhooks_does_nothing_if_webhook_exists()
    {
        // 1. ARRANGE (Preparar)

        // A. Definimos la URL objetivo
        $target_url_pretty = 'https://sitioprueba.com/wc-api/Openpay_Stores';
        $target_url_simple = 'https://sitioprueba.com/index.php?wc-api=Openpay_Stores';

        // B. Configuramos el mock del webhook "activo"
        $mockActiveWebhook = $this->createMock(OpenpayWebhook::class);
        $mockActiveWebhook->method('__get')
            ->willReturnMap([
                ['id', 'wh_activo_9999'],
                ['url', $target_url_pretty] // ¡La URL coincide exactamente!
            ]);

        // C. Configuramos el mock del recurso $openpay->webhooks

        // "Cuando se llame a getList(), devuelve el webhook activo"
        $this->mockWebhookResource->method('getList')
            ->willReturn([$mockActiveWebhook]);

        // "Esperamos que el método 'add' (crear) NUNCA sea llamado"
        $this->mockWebhookResource->expects($this->never())
            ->method('add');

        // "Esperamos que el método 'get' (borrar) NUNCA sea llamado"
        $this->mockWebhookResource->expects($this->never())
            ->method('get');

        // E. Instanciamos nuestro servicio
        $service = new OpenpayWebhookService($this->mockOpenpayApi, $this->mockLogger);

        // 2. ACT (Actuar)
        $result = $service->reconcileWebhooks($target_url_pretty, $target_url_simple);

        // 3. ASSERT (Verificar)
        $this->assertEquals('found', $result['status']);
        $this->assertEquals('wh_activo_9999', $result['id']);
    }

    /**
     * Prueba que el método findWebhookByUrl devuelve el objeto webhook correcto
     * si la URL se encuentra en la lista.
     */
    public function test_findWebhookByUrl_returns_webhook_if_found()
    {
        // 1. ARRANGE (Preparar)

        // A. Definimos la URL que vamos a buscar
        $target_url = 'https://sitioprueba.com/hook-correcto';

        // B. Creamos un mock del webhook que SÍ coincide
        $mockFoundWebhook = $this->createMock(OpenpayWebhook::class);
        $mockFoundWebhook->method('__get')
            ->willReturnMap([
                ['id', 'wh_encontrado_123'],
                ['url', $target_url]
            ]);

        // C. Creamos un mock de "otro" webhook que NO coincide
        $mockOtherWebhook = $this->createMock(OpenpayWebhook::class);
        $mockOtherWebhook->method('__get')
            ->with($this->equalTo('url'))
            ->willReturn('https://sitio-incorrecto.com/hook');

        // D. Configuramos el mock de getList()
        // El método findWebhookByUrl llama a getList([])
        $this->mockWebhookResource->method('getList')
            ->with($this->equalTo([])) // Verifica que se llame con un array vacío
            ->willReturn([$mockOtherWebhook, $mockFoundWebhook]);

        // E. Instanciamos el servicio
        $service = new OpenpayWebhookService($this->mockOpenpayApi, $this->mockLogger);

        // 2. ACT (Actuar)
        $result = $service->findWebhookByUrl($target_url);

        // 3. ASSERT (Verificar)
        // Verificamos que el resultado no sea null
        $this->assertNotNull($result);
        // Verificamos que sea el objeto exacto que simulamos
        $this->assertSame($mockFoundWebhook, $result);
        // Verificamos que el ID sea el correcto (usando el __get simulado)
        $this->assertEquals('wh_encontrado_123', $result->id);
    }

    /**
     * Prueba que el método findWebhookByUrl devuelve null si la URL no se encuentra.
     * @dataProvider provideNotFoundScenarios
     */
    public function test_findWebhookByUrl_returns_null_if_not_found(array $mockedList)
    {
        // 1. ARRANGE (Preparar)

        // A. Definimos la URL que vamos a buscar
        $target_url = 'https://url-que-no-existe.com';

        // B. Configuramos el mock de getList() para que devuelva la lista del DataProvider
        $this->mockWebhookResource->method('getList')
            ->with($this->equalTo([]))
            ->willReturn($mockedList);

        // C. Instanciamos el servicio
        $service = new OpenpayWebhookService($this->mockOpenpayApi, $this->mockLogger);

        // 2. ACT (Actuar)
        $result = $service->findWebhookByUrl($target_url);

        // 3. ASSERT (Verificar)
        // Verificamos que el resultado sea null
        $this->assertNull($result);
    }

    /**
     * Proveedor de datos para test_findWebhookByUrl_returns_null_if_not_found
     * Esto nos permite probar dos casos con un solo test:
     * 1. La lista de webhooks está vacía.
     * 2. La lista tiene webhooks, pero ninguno coincide.
     */
    public function provideNotFoundScenarios()
    {
        // Caso 1: La lista está vacía
        $emptyList = [];

        // Caso 2: La lista tiene webhooks que no coinciden
        $mockOtherWebhook = $this->createMock(OpenpayWebhook::class);
        $mockOtherWebhook->method('__get')
            ->with($this->equalTo('url'))
            ->willReturn('https://sitio-incorrecto.com/hook');

        $listWithOtherWebhooks = [$mockOtherWebhook];

        // Devolvemos los escenarios
        return [
            'lista vacía' => [$emptyList],
            'lista no coincide' => [$listWithOtherWebhooks],
        ];
    }

    /**
     * Prueba que deleteWebhook lanza una excepción formateada si la API falla.
     * Cubre: catch (\Exception $e) { throw new \Exception(...); }
     */
    public function test_deleteWebhook_throws_custom_exception_on_api_failure()
    {
        // 1. ARRANGE
        $webhookId = 'wh_fail_123';

        // Simulamos que al buscar el webhook para borrarlo, la API falla
        $this->mockWebhookResource->method('get')
            ->with($this->equalTo($webhookId))
            ->willThrowException(new \Exception('Error de conexión simulado'));

        $service = new OpenpayWebhookService($this->mockOpenpayApi, $this->mockLogger);

        // 2. ASSERT (Esperamos la excepción)
        $this->expectException(\Exception::class);
        // Verificamos que el mensaje sea el que concatenaste en tu código
        $this->expectExceptionMessage('Error al intentar eliminar el webhook wh_fail_123: Error de conexión simulado');

        // 3. ACT
        $service->deleteWebhook($webhookId);
    }

    /**
     * Prueba que un webhook ajeno (URL no coincide con el plugin) es ignorado.
     * Cubre la línea del ELSE: "Webhook ignorado (no coincide con el endpoint)".
     */
    public function test_reconcileWebhooks_ignores_unrelated_webhooks()
    {
        // 1. ARRANGE
        $target_url = 'https://mi-sitio.com/wc-api/Openpay_Stores';
        $target_url_simple = 'https://mi-sitio.com/index.php?wc-api=Openpay_Stores';

        // URL AJENA
        $alienUrl = 'https://google.com/foo/bar';

        // Usamos stdClass para el webhook
        $alienWebhook = new \stdClass();
        $alienWebhook->id = 'wh_alien_999';
        $alienWebhook->url = $alienUrl;

        // Configuramos la lista
        $this->mockWebhookResource->method('getList')->willReturn([$alienWebhook]);

        // Mock de creación (necesario para que el flujo no se rompa después)
        $mockNewWebhook = $this->createMock(OpenpayWebhook::class);
        $mockNewWebhook->method('__get')->with('id')->willReturn('wh_new');
        $this->mockWebhookResource->method('add')->willReturn($mockNewWebhook);

        // En lugar de fallar en el primer log, capturamos TODOS los logs en un array.
        $capturedLogs = [];
        $this->mockLogger->method('info')->will($this->returnCallback(function ($message) use (&$capturedLogs) {
            $capturedLogs[] = $message;
            return true;
        }));

        $service = new OpenpayWebhookService($this->mockOpenpayApi, $this->mockLogger);

        // 2. ACT
        $service->reconcileWebhooks($target_url, $target_url_simple);

        // 3. ASSERT MANUAL
        // Buscamos si alguno de los mensajes capturados es el que esperamos
        $found = false;
        foreach ($capturedLogs as $log) {
            if (strpos($log, 'Webhook ignorado') !== false) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'El logger no recibió el mensaje: "Webhook ignorado". Logs recibidos: ' . json_encode($capturedLogs));
    }

    /**
     * Prueba que si falla la eliminación de un webhook obsoleto, se registra en el log.
     * Cubre: catch (\Exception $e) { $this->logger->error('Error al intentar eliminar...'); }
     */
    public function test_reconcileWebhooks_logs_error_when_deletion_fails()
    {
        // 1. ARRANGE
        $target_url = 'https://nuevo-sitio.com/wc-api/Openpay_Stores';

        // Simulamos un webhook obsoleto (dominio viejo, path correcto)
        $obsoleteUrl = 'https://viejo-sitio.com/wc-api/Openpay_Stores';
        $mockObsoleteWebhook = $this->createMock(OpenpayWebhook::class);
        $mockObsoleteWebhook->method('__get')->willReturnMap([
            ['url', $obsoleteUrl],
            ['id', 'wh_obsoleto_fail']
        ]);

        $this->mockWebhookResource->method('getList')->willReturn([$mockObsoleteWebhook]);

        // CLAVE: Cuando el código llame a deleteWebhook -> get('wh_obsoleto_fail'), lanzamos excepción
        $this->mockWebhookResource->method('get')
            ->with('wh_obsoleto_fail')
            ->willThrowException(new \Exception('Fallo al borrar'));

        // Permitimos que continúe y cree el nuevo
        $mockNewWebhook = $this->createMock(OpenpayWebhook::class);
        $mockNewWebhook->method('__get')->with('id')->willReturn('wh_new');
        $this->mockWebhookResource->method('add')->willReturn($mockNewWebhook);

        // EXPECTATIVA: El logger debe registrar un ERROR (no info) capturando la excepción
        $this->mockLogger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error al intentar eliminar el webhook obsoleto'));

        $service = new OpenpayWebhookService($this->mockOpenpayApi, $this->mockLogger);

        // 2. ACT
        $service->reconcileWebhooks($target_url, 'https://simple.url');
    }
}