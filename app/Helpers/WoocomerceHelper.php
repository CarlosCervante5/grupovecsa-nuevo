<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class WoocomerceHelper
{
    /**
     * Crear cupón de vecsa boutique
     *
     * @param string $webhookUrl La URL de la api de VecsaBoutique.
     * @param array ...$dataArrays Arreglos de datos a enviar.
     * @return bool
     */
    public static function createCoupon(string $consumerKey, string $consumerSecret , string $webhookUrl, array $data)
    {

        // Enviar los datos a Grupovecsa Boutique mediante HTTP POST
        $response = Http::withBasicAuth($consumerKey, $consumerSecret)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($webhookUrl, $data);

        // Devolver true si la solicitud fue exitosa, de lo contrario false
        return $response->successful();
    }

    /**
     * Obtener la URL del webhook desde el .env.
     *
     * @param string $key La clave de la variable de entorno.
     * @return string|null
     */
    public static function getEnvironmentKey(string $key)
    {
        return env($key);
    }
}
