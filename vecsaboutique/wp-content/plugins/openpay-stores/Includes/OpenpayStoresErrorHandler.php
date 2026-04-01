<?php
namespace OpenpayStores\Includes;

use Exception;
use Openpay\Data\OpenpayApiError;
use Openpay\Data\OpenpayApiConnectionError;
use Openpay\Data\OpenpayApiTransactionError;
use OpenpayStores\Includes\OpenpayStoresErrorManager;

class OpenpayStoresErrorHandler
{
    protected static $logger;

    public static function init()
    {
        self::$logger = wc_get_logger();
    }

    public static function log($message, $context = [])
    {
        if (!self::$logger) {
            self::$logger = wc_get_logger();
        }
        $context = is_array($context) ? $context : [];
        if (self::$logger) {
            self::$logger->error($message, $context);
        }
    }

    public static function handleOpenpayStorePluginException($exception, $order_id = null, $customer_id = null)
    {
        if ($exception instanceof OpenpayApiTransactionError || $exception instanceof OpenpayApiError || $exception instanceof OpenpayApiConnectionError) {
            $openpayErrorManager = new OpenpayStoresErrorManager();
            $errorMessage = $openpayErrorManager::getErrorMessages($exception->getCode());
            $message = "[Openpay ERROR] " . $errorMessage['logError'];
            $uuid = self::generate_uuid_v4();
            $context = [
                'id' => $uuid,
                'order_id' => ($order_id == null) ? 'no_order_id' : $order_id,
                'code' => $exception->getCode(),
                'user_id' => ($customer_id === null) ? 'guest' : $customer_id,
                'gateway' => 'openpay-stores',
            ];

            if ($order_id != null) {
                $order = wc_get_order($order_id);
                $order->add_order_note($errorMessage['orderDetailError']);
                $order->update_status('failed');
            }
        } else {
            $message = "[EXCEPTION] " . $exception->getMessage();
            $context = [];
        }
        self::log($message, $context);
    }

    public static function catchOpenpayStoreError($callback, $order_id = null, $customer_id = null)
    {
        $logger = wc_get_logger();
        try {
            return $callback();
        } catch (OpenpayApiConnectionError $e) {
            $logger->info('[OpenpayStoresErrorHandler.catchOpenpayStoreError - OpenpayApiConnectionError] ');
            self::handleOpenpayStorePluginException($e, $order_id, $customer_id);
            $openpayErrorManager = new OpenpayStoresErrorManager();
            $errorMessage = $openpayErrorManager::getErrorMessages($e->getCode());
            throw new Exception($errorMessage['clientError'], $e->getCode());
        } catch (OpenpayApiTransactionError $e) {
            $logger->info('[OpenpayStoresErrorHandler.catchOpenpayStoreError - OpenpayApiTransactionError] ');
            self::handleOpenpayStorePluginException($e, $order_id, $customer_id);
            $openpayErrorManager = new OpenpayStoresErrorManager();
            $errorMessage = $openpayErrorManager::getErrorMessages($e->getCode());
            throw new Exception($errorMessage['clientError'], $e->getCode());
        } catch (OpenpayApiError $e) {
            $logger->info('[OpenpayStoresErrorHandler.catchOpenpayStoreError - OpenpayApiError] ');
            $logger->info('[OpenpayStoresErrorHandler.catchOpenpayStoreError]' . json_encode($e));
            self::handleOpenpayStorePluginException($e, $order_id, $customer_id);
            $openpayErrorManager = new OpenpayStoresErrorManager();
            $errorMessage = $openpayErrorManager::getErrorMessages($e->getCode());
            throw new Exception($errorMessage['clientError'], $e->getCode());
        }
        return false;
    }

    public static function generate_uuid_v4()
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}