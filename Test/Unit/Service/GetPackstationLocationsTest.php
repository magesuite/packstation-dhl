<?php

declare(strict_types=1);

namespace MageSuite\PacstationDhl\Test\Unit\Service;

class GetPackstationLocationsTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\PackstationDhl\Service\GetPackstationLocations $getPackstationLocations;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->getPackstationLocations = $objectManager->get(\MageSuite\PackstationDhl\Service\GetPackstationLocations::class);
    }

    public function testItReturnsCorrectParameter(): void
    {
        $zip = '12345';
        $parameter = $this->getPackstationLocations->prepareCallParameter($zip);
        $this->assertEquals($zip, $parameter['postalCode']);
    }
}
