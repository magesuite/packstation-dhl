<?php

declare(strict_types=1);

namespace MageSuite\PackstationDhl\Test\Integration\Controller\Packstation;

/**
 * @magentoAppArea frontend
 * @magentoConfigFixture current_store carriers/dhl_packstation/active 1
 * @magentoConfigFixture current_store carriers/dhl_packstation/country_code DE
 */
class ListingTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected \PHPUnit\Framework\MockObject\MockObject $dhlApiClientMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dhlApiClientMock = $this->createMock(\MageSuite\PackstationDhl\Service\DhlApiClient::class);
        $this->_objectManager->addSharedInstance(
            $this->dhlApiClientMock,
            \MageSuite\PackstationDhl\Service\DhlApiClient::class
        );
    }

    public function testItReturnsPackstationListForValidZip(): void
    {
        $apiResponse = $this->getPackstationApiResponse();

        $this->dhlApiClientMock
            ->expects($this->once())
            ->method('getPackstationsByAddress')
            ->willReturn($apiResponse);

        $this->dispatch('dhl/packstation/listing?zip=10115');

        $response = json_decode($this->getResponse()->getBody(), true);

        $this->assertCount(1, $response);
        $this->assertEquals('Packstation 101', $response[0]['address']['addressLocality']);
        $this->assertEquals('10115', $response[0]['place']['address']['postalCode']);
        $this->assertEquals('DHL Packstation 101', $response[0]['name']);
    }

    public function testItReturnsNullForInvalidZip(): void
    {
        $this->dhlApiClientMock
            ->expects($this->never())
            ->method('getPackstationsByAddress');

        $this->dispatch('dhl/packstation/listing?zip=abc');

        $response = json_decode($this->getResponse()->getBody(), true);

        $this->assertNull($response);
    }

    private function getPackstationApiResponse(): array
    {
        return [
            'locations' => [
                [
                    'url' => '/locations/packstation-101',
                    'location' => [
                        'ids' => [
                            ['locationId' => '101', 'provider' => 'dhl']
                        ],
                        'keyword' => 'packstation',
                        'type' => 'locker'
                    ],
                    'name' => 'DHL Packstation 101',
                    'address' => [
                        'countryCode' => 'DE',
                        'postalCode' => '10115',
                        'addressLocality' => 'Packstation 101',
                        'streetAddress' => 'Friedrichstraße 10'
                    ],
                    'place' => [
                        'address' => [
                            'countryCode' => 'DE',
                            'postalCode' => '10115',
                            'addressLocality' => 'Berlin',
                            'streetAddress' => 'Friedrichstraße 10'
                        ],
                        'geo' => [
                            'latitude' => 52.5200,
                            'longitude' => 13.3880
                        ]
                    ],
                    'serviceTypes' => ['parcel:pick-up-all', 'parcel:drop-off'],
                    'openingHours' => [
                        ['dayOfWeek' => 'http://schema.org/Monday', 'opens' => '00:00', 'closes' => '23:59'],
                        ['dayOfWeek' => 'http://schema.org/Tuesday', 'opens' => '00:00', 'closes' => '23:59']
                    ]
                ]
            ]
        ];
    }
}
