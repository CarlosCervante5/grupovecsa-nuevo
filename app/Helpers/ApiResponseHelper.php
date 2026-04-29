<?php
namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ApiResponseHelper
{
    public static function authSuccess($status = 200, $message, $data = null)
    {
        self::loginSuccess($message, $data);
        return response()->json(['status' => $status, 'message' => $message, 'data' => $data], $status);
    }

    public static function authError($userMessage, $error = null, $status = 500, $errorCode = null)
    {
        $logMessage = $errorCode ? "[Error Code: $errorCode] $userMessage" : $userMessage;
        self::loginError($logMessage, $error);

        return response()->json(['status' => $status, 'message' => $userMessage, 'data' => null], $status);
    }

    private static function loginSuccess($message, $data)
    {
        Log::channel('loginSuccess')->info($message, ['data' => $data]);
    }

    private static function loginError($message, $error)
    {
        Log::channel('loginError')->error($message, ['error' => $error]);
    }

    public static function apiSuccess($status = 200, $message, $data = null)
    {
        self::resourceSuccess($message, $data);
        return response()->json(['status' => $status, 'message' => $message, 'data' => $data], $status);
    }

    public static function apiError($userMessage, $error = null, $status = 500, $errorCode = null)
    {
        $logMessage = $errorCode ? "[Error Code: $errorCode] $userMessage" : $userMessage;
        self::resourceError($logMessage, $error);

        $genericMessage = 'Hubo un problema con su solicitud: ' . $userMessage;
        return response()->json(['status' => $status, 'message' => $genericMessage, 'data' => null], $status);
    }

    public static function imageSuccess($status = 200, $message, $data = null)
    {
        self::logImageSuccess($message, $data);
        return response()->json(['status' => $status, 'message' => $message, 'data' => $data], $status);
    }

    public static function imageError($userMessage, $error = null, $status = 500, $errorCode = null)
    {
        $logMessage = $errorCode ? "[Error Code: $errorCode] $userMessage" : $userMessage;
        self::logImageError($logMessage, $error);

        $genericMessage = 'Hubo un problema con su solicitud: ' . $userMessage;
        return response()->json(['status' => $status, 'message' => $genericMessage, 'data' => null], $status);
    }

    public static function validationError(ValidationException $e)
    {
        $errors = $e->errors();
        return response()->json(['status' => 422, 'message' => 'Error de validación', 'errors' => $errors], 422);
    }

    private static function resourceSuccess($message, $data)
    {
        Log::channel('apiSuccess')->info($message, ['data' => $data]);
    }

    private static function resourceError($message, $error)
    {
        Log::channel('apiError')->error($message, ['error' => $error]);
    }

    private static function logImageSuccess($message, $data)
    {
        Log::channel('imageSuccess')->info($message, ['data' => $data]);
    }

    private static function logImageError($message, $error)
    {
        Log::channel('imageError')->error($message, ['error' => $error]);
    }
}
