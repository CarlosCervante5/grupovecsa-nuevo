<?php
namespace OpenpayStores\Includes;

class OpenpayStoresErrorManager
{
    private static $errors = [
        1000 => [
            'clientError' => 'Servicio no disponible.',
            'adjustedError' => 'Lo sentimos, no pudimos completar el proceso de pago. Estamos trabajando para solucionarlo. Disculpa las molestias.',
            'orderDetailError' => 'Ocurrió un error interno en el servidor de Openpay',
            'logError' => 'Internal server error, contact support'
        ],
        1003 => [
            'clientError' => 'Petición con parámetros incorrectos.',
            'adjustedError' => 'La conexión segura no pudo establecerse. Hubo un problema al intentar comunicar tu pago. Por favor, intenta de nuevo en unos minutos.',
            'orderDetailError' => 'La instancia no tiene certificado de seguridad para https ó no estan disponibles lo puertos válidos',
            'logError' => 'Parameters look valid but request failed'
        ],
        1004 => [
            'clientError' => 'Servicio no disponible.',
            'adjustedError' => 'Disculpa la molestia. En este momento, uno de nuestros sistemas de soporte no está disponible.',
            'orderDetailError' => 'Un servicio necesario para el procesamiento de la transacción no se encuentra disponible.',
            'logError' => 'The resource is unavailable at this moment. Please try again later'
        ],
        1005 => [
            'clientError' => 'Servicio no disponible.',
            'adjustedError' => 'No se pudo encontrar la información para completar esta acción. Por favor, verifica que todos los campos requeridos estén llenos',
            'orderDetailError' => 'Uno de los recursos requeridos no existe.',
            'logError' => 'The requested resource doesnt exist'
        ],
        6001 => [
            'clientError' => 'El webhook ya ha sido procesado.',
            'adjustedError' => 'Hubo un problema temporal con nuestro sistema. Por favor, intenta de nuevo más tarde o contáctanos si el problema persiste',
            'orderDetailError' => 'Se intento volver a registrar un webhook que ya estaba registrado',
            'logError' => 'The webhook has already been processed'
        ],
        6002 => [
            'clientError' => 'No se ha podido conectar con el servicio de webhook.',
            'adjustedError' => 'No pudimos completar la operación debido a un problema temporal. Por favor, intenta de nuevo en un momento. Disculpa la molestia.',
            'orderDetailError' => 'Hubo algún conflicto ó fallo alguna condición',
            'logError' => 'Could not connect with webhook service, verify URL Service responded with an error on this moment. Please try again later'
        ],
        6003 => [
            'clientError' => 'No se ha podido conectar con el servicio de webhook.',
            'adjustedError' => 'No pudimos completar la operación debido a un problema temporal. Por favor, intenta de nuevo en un momento. Disculpa la molestia.',
            'orderDetailError' => 'Hubo algún conflicto ó fallo alguna condición',
            'logError' => 'Could not connect with webhook service, verify URL Service responded with an error on this moment. Please try again later'
        ],
        1012 => [
            'clientError' => 'El monto ingresado es demasiado alto. El monto máximo permitido es de $29,999.00',
            'adjustedError' => 'El monto deber ser menor a 29999',
            'orderDetailError' => 'El monto deber ser menor a 29999',
            'logError' => 'The amount must be less than 29999'
        ],
    ];

    public static function getErrorMessages($code)
    {
        if (isset(self::$errors[$code])) {
            return self::$errors[$code];
        }

        return [
            'clientError' => 'Ha ocurrido un error inesperado',
            'adjustedError' => 'Ha ocurrido un error inesperado',
            'orderDetailError' => 'Ha ocurrido un error inesperado',
            'logError' => 'Ha ocurrido un error inesperado'
        ];
    }
}