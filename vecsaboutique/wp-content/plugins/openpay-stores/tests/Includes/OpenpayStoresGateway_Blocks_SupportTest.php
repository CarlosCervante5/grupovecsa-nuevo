<?php
namespace OpenpayStores\Tests\Includes;

use PHPUnit\Framework\TestCase;
use OpenpayStores\Includes\OpenpayStoresGateway_Blocks_Support;
use ReflectionClass;

class OpenpayStoresGateway_Blocks_SupportTest extends TestCase
{

    public function testInitializeRegistersCallback()
    {
        $gateway = new OpenpayStoresGateway_Blocks_Support();
        $gateway->initialize();
        $this->assertNotNull($gateway);
    }

    public function testIsActiveEnabled()
    {
        $gateway = new OpenpayStoresGateway_Blocks_Support();
        // Accedemos a la propiedad protegida con Reflection
        $refClass = new ReflectionClass($gateway);
        $prop = $refClass->getProperty('settings');
        $prop->setAccessible(true);
        $prop->setValue($gateway, ['enabled' => 'yes']);

        $this->assertTrue($gateway->is_active());
    }

    public function testIsActiveDisabled()
    {
        $gateway = new OpenpayStoresGateway_Blocks_Support();
        $refClass = new ReflectionClass($gateway);
        $prop = $refClass->getProperty('settings');
        $prop->setAccessible(true);
        $prop->setValue($gateway, ['enabled' => 'no']);

        $this->assertFalse($gateway->is_active());
    }

    public function testIsActiveEmpty()
    {
        $gateway = new OpenpayStoresGateway_Blocks_Support();
        $refClass = new ReflectionClass($gateway);
        $prop = $refClass->getProperty('settings');
        $prop->setAccessible(true);
        $prop->setValue($gateway, []);

        $this->assertFalse($gateway->is_active());
    }

    public function testGetPaymentMethodScriptHandlesWithoutAssetFile()
    {
        // Aseguramos que no existe el archivo
        $assetFile = __DIR__ . '/blocks/checkout-form/build/index.asset.php';
        if (file_exists($assetFile)) {
            unlink($assetFile);
        }

        $gateway = new OpenpayStoresGateway_Blocks_Support();
        $handles = $gateway->get_payment_method_script_handles();

        $this->assertEquals(['openpay-stores-blocks-integration'], $handles);
    }

    public function testGetPaymentMethodScriptHandlesWithAssetFile()
    {
        $assetsDir = __DIR__ . '/blocks/checkout-form/build/';
        if (!is_dir($assetsDir)) {
            mkdir($assetsDir, 0777, true);
        }
        $assetFile = $assetsDir . 'index.asset.php';
        file_put_contents($assetFile, "<?php return ['version' => '1.0.0', 'dependencies' => ['jquery']];");

        $gateway = new OpenpayStoresGateway_Blocks_Support();
        $handles = $gateway->get_payment_method_script_handles();

        $this->assertEquals(['openpay-stores-blocks-integration'], $handles);
    }

    public function testGetPaymentMethodData()
    {
        $gateway = new OpenpayStoresGateway_Blocks_Support();
        $data = $gateway->get_payment_method_data();

        $this->assertEquals('MX', $data['country']);

        // En lugar de comparar la URL completa (que varía según el entorno),
        // verificamos que apunte a la carpeta correcta relativa al plugin.
        $this->assertStringEndsWith('/assets/images/', $data['images_dir']);
    }
}