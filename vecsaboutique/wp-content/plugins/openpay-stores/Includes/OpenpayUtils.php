<?php

namespace OpenpayStores\Includes;

class OpenpayUtils
{
    public static function isNullOrEmptyString($string){
        return (!isset($string) || trim($string) === '');
    }

    public static function getCurrencies($countryCode)
    {
        switch ($countryCode) {
            case 'MX':
                return ['MXN'];
            case 'CO':
                return ['COP'];
            case 'PE':
                return ['PEN'];
            default:
                break;
        }
    }

    public static function getUrlPdfBase($isSandbox, $countryCode)
    {
        $countryCode = strtolower($countryCode);
        $sandbox = 'https://sandbox-dashboard.openpay.' . $countryCode . '/paynet-pdf';
        $production = 'https://dashboard.openpay.' . $countryCode . '/paynet-pdf';
        $pdfBase = ($isSandbox) ? $sandbox : $production;
        return $pdfBase;
    }

    public static function getCountryName($countryCode)
    {
        switch ($countryCode) {
            case 'MX':
                return 'Mexico';
            case 'CO':
                return 'Colombia';
            case 'PE':
                return 'Peru';
            default:
                break;
        }
    }

    public static function getMessageError($countryName, $currency)
    {
        $format = 'Openpay Stores Plugin %s is only available for %s currency.';
        return sprintf($format, $countryName, $currency);
    }
}
