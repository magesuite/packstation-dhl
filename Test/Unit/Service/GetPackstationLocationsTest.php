<?php

namespace MageSuite\PacstationDhl\Test\Unit\Service;

class GetPackstationLocationsTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \MageSuite\PackstationDhl\Service\GetPackstationLocations
     */
    protected $getPackstationLocations;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->getPackstationLocations = $objectManager->get(\MageSuite\PackstationDhl\Service\GetPackstationLocations::class);
    }

    public function testItReturnsCorrectParameter()
    {
        $zip = 12345;
        $parameter = $this->getPackstationLocations->prepareCallParameter($zip);

        $this->assertEmpty($parameter->key);
        $this->assertEquals($zip, $parameter->address['zip']);
    }
}
