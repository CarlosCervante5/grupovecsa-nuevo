<?php
namespace OpenpayStores\Includes;

use Openpay\Data\Openpay;
use Openpay\Data\OpenpayApi;

class OpenpayClient
{

	/**
	 * Configura el entorno global de Openpay y devuelve la instancia de la API.
	 *
	 * @return OpenpayApi
	 */
	public static function getInstance(string $merchant_id, string $private_key, string $country, bool $is_sandbox): OpenpayApi
	{

		Openpay::setClassificationMerchant('general');
		Openpay::setProductionMode(!$is_sandbox);

		$openpay = Openpay::getInstance($merchant_id, $private_key, $country, self::getClientIp());

		$userAgent = "Openpay-WOOC" . strtoupper($country) . "/v2";
		Openpay::setUserAgent($userAgent);

		return $openpay;
	}

	/**
	 * Obtiene la IP del cliente o un fallback si se ejecuta en segundo plano (webhook handler).
	 * versión más robusta que maneja correctamente las listas de proxy.
	 *
	 * @return string
	 */
	private static function getClientIp()
	{
		$logger = function_exists('wc_get_logger') ? wc_get_logger() : null;

		$ipAdress = '';
		$ipSources = [
			$_SERVER['HTTP_CLIENT_IP'] ?? null,
			$_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
			$_SERVER['REMOTE_ADDR'] ?? null
		];

		// Buscar la primera fuente de IP disponible
		foreach ($ipSources as $source) {
			if (!empty($source)) {
				$ipAdress = $source;

				// Si es una lista (de proxy), tomar la primera IP VÁLIDA
				$ipList = explode(",", $ipAdress);
				$ipAdress = '';

				foreach ($ipList as $ip) {
					$ip = trim($ip);
					if (filter_var($ip, FILTER_VALIDATE_IP)) {
						$ipAdress = $ip; // Encontramos la primera IP real
						break;
					}
				}

				if ($ipAdress) {
					break; // Salir del loop principal de fuentes
				}
			}
		}

		// Si, después de todo, sigue vacía (como en WP-Cron), usamos un fallback.
		if (empty($ipAdress)) {
			if ($logger)
				$logger->debug('[OpenpayClient.getClientIp] IP de header vacía o inválida. Usando fallback de servidor.');

			if (!empty($_SERVER['SERVER_ADDR'])) {
				$ipAdress = $_SERVER['SERVER_ADDR'];
			} else {
				// Fallback absoluto si todo lo demás falla
				$ipAdress = '127.0.0.1';
			}
		}

		return $ipAdress;
	}

}