<?php
namespace OpenpayStores\Tests\Includes;

use OpenpayStores\Includes\OpenpayUtils;
use PHPUnit\Framework\TestCase;

class OpenpayUtilsTest extends TestCase
{
    /** @test */
    public function testIsNullOrEmptyString()
    {
        $this->assertTrue(OpenpayUtils::isNullOrEmptyString(null));
        $this->assertTrue(OpenpayUtils::isNullOrEmptyString(''));
        $this->assertTrue(OpenpayUtils::isNullOrEmptyString('   '));
        $this->assertFalse(OpenpayUtils::isNullOrEmptyString('Hola'));
    }

    /** @test */
    public function testGetCurrencies()
    {
        $this->assertEquals(['MXN'], OpenpayUtils::getCurrencies('MX'));
        $this->assertEquals(['COP'], OpenpayUtils::getCurrencies('CO'));
        $this->assertEquals(['PEN'], OpenpayUtils::getCurrencies('PE'));
        $this->assertNull(OpenpayUtils::getCurrencies('US')); // default case
    }

    /** @test */
    public function testGetUrlPdfBaseSandbox()
    {
        $url = OpenpayUtils::getUrlPdfBase(true, 'MX');
        $this->assertEquals('https://sandbox-dashboard.openpay.mx/paynet-pdf', $url);
    }

    /** @test */
    public function testGetUrlPdfBaseProduction()
    {
        $url = OpenpayUtils::getUrlPdfBase(false, 'CO');
        $this->assertEquals('https://dashboard.openpay.co/paynet-pdf', $url);
    }

    /** @test */
    public function testGetCountryName()
    {
        $this->assertEquals('Mexico', OpenpayUtils::getCountryName('MX'));
        $this->assertEquals('Colombia', OpenpayUtils::getCountryName('CO'));
        $this->assertEquals('Peru', OpenpayUtils::getCountryName('PE'));
        $this->assertNull(OpenpayUtils::getCountryName('US')); // default case
    }

    /** @test */
    public function testGetMessageError()
    {
        $msg = OpenpayUtils::getMessageError('Mexico', 'MXN');
        $this->assertEquals('Openpay Stores Plugin Mexico is only available for MXN currency.', $msg);
    }
}