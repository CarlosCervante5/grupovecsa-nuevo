<?php

namespace App\Helpers;

class PasswordHelper 
{

    /**
     * Generar password según reglas de validación: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
     *
     * @param int $length del password a generar. Por defecto 10 elementos
     * @return string
     */
    public static function generateSecurePassword($length = 10)
    {
        // Define los conjuntos de caracteres
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';
        $specialCharacters = '@$!%*?&';

        // Asegura que cada conjunto esté presente en la contraseña
        $password = [
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $specialCharacters[random_int(0, strlen($specialCharacters) - 1)]
        ];

        // Genera caracteres adicionales aleatorios para alcanzar la longitud deseada
        $remainingLength = $length - count($password);
        $allCharacters = $lowercase . $uppercase . $digits . $specialCharacters;

        for ($i = 0; $i < $remainingLength; $i++) {
            $password[] = $allCharacters[random_int(0, strlen($allCharacters) - 1)];
        }

        // Mezcla los caracteres para obtener una contraseña aleatoria
        shuffle($password);

        // Convierte el array de caracteres en una cadena
        return implode('', $password);
    }

}