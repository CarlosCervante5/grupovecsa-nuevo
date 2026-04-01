<?php

use OpenpayStores\Includes\OpenpayStoresErrorManager;

/**
 * Pruebas unitarias para OpenpayStoresErrorManager.
 *
 * @covers \OpenpayStores\Includes\OpenpayStoresErrorManager
 */
class OpenpayStoresErrorManagerTest extends \WP_UnitTestCase
{
    /**
     * Prueba que un código de error conocido devuelva el array de mensajes correcto.
     */
    public function test_getErrorMessages_returns_specific_error_for_known_code()
    {
        $code = 1003;
        $messages = OpenpayStoresErrorManager::getErrorMessages($code);

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('clientError', $messages);
        $this->assertEquals('Petición con parámetros incorrectos.', $messages['clientError']);
        $this->assertEquals('La instancia no tiene certificado de seguridad para https ó no estan disponibles lo puertos válidos', $messages['orderDetailError']);
    }

    /**
     * Prueba que un código de error específico (ej. 6001) devuelva sus mensajes correctos.
     */
    public function test_getErrorMessages_returns_correct_error_for_6001()
    {
        $code = 6001;
        $messages = OpenpayStoresErrorManager::getErrorMessages($code);

        $this->assertIsArray($messages);
        $this->assertEquals('El webhook ya ha sido procesado.', $messages['clientError']);
        $this->assertEquals('The webhook has already been processed', $messages['logError']);
    }

    /**
     * Prueba que un código de error desconocido devuelva el array de mensajes por defecto.
     */
    public function test_getErrorMessages_returns_default_error_for_unknown_code()
    {
        $code = 9999; // Un código que no existe en la clase
        $messages = OpenpayStoresErrorManager::getErrorMessages($code);

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('clientError', $messages);
        $this->assertEquals('Ha ocurrido un error inesperado', $messages['clientError']);
        $this->assertEquals('Ha ocurrido un error inesperado', $messages['logError']);
    }
}