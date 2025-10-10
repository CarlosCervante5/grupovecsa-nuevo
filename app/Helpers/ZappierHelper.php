<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class ZappierHelper
{
    /**
     * Enviar datos a Zappier.
     *
     * @param string $webhookUrl La URL del webhook de Zappier.
     * @param array ...$dataArrays Arreglos de datos a enviar.
     * @return bool
     */
    public static function sendToZappier(string $webhookUrl, array ...$dataArrays)
    {
        // Combina todos los arreglos de datos en uno solo
        $zappier = array_merge(...$dataArrays);

        // Enviar los datos a Zappier mediante HTTP POST
        $response = Http::post($webhookUrl, $zappier);

        // Devolver true si la solicitud fue exitosa, de lo contrario false
        return $response->successful();
    }

    /**
     * Obtener la URL del webhook desde el .env.
     *
     * @param string $key La clave de la variable de entorno.
     * @return string|null
     */
    public static function getWebhookUrl(string $key)
    {
        return env($key);
    }
}
