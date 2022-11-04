<?php

namespace MageSuite\PackstationDhl\Service;

class GetPackstationLocations
{
    const API_ZIP_FIELD = 'postalCode';

    const API_COUNTRY_CODE_FIELD = 'countryCode';

    const API_SERVICE_TYPE_FIELD = 'serviceType';

    const API_LOCATION_TYPE_FIELD = 'locationType';

    const API_RADIUS_FIELD = 'radius';

    const API_LIMIT_FIELD = 'limit';

    const API_RESPONSE_LOCATION_FIELD = 'locations';

    protected \MageSuite\PackstationDhl\Service\DhlApiClient $dhlApiClient;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(
        \MageSuite\PackstationDhl\Service\DhlApiClient $dhlApiClient,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->dhlApiClient = $dhlApiClient;
    }

    public function execute(string $zip):? array
    {
        if (!is_numeric($zip) || strlen($zip) < 2 || strlen($zip) > 5) {
            return null;
        }

        try {
            $requestData = $this->prepareCallParameter($zip);
            $response = $this->dhlApiClient->getPackstationsByAddress($requestData);

            return $response[self::API_RESPONSE_LOCATION_FIELD];
        } catch (\MageSuite\PackstationDhl\Exception\ApiException $e) {
            $this->logger->error('DHL Packstation resolver error: ' . $e->getMessage());
        }

        return null;
    }

    public function prepareCallParameter($zip): array
    {
        return [
            self::API_COUNTRY_CODE_FIELD => 'DE',
            self::API_LOCATION_TYPE_FIELD => 'locker',
            self::API_SERVICE_TYPE_FIELD => 'parcel:pick-up-all',
            self::API_RADIUS_FIELD => '1000000',
            self::API_LIMIT_FIELD => 50,
            self::API_ZIP_FIELD => $zip
        ];
    }
}
