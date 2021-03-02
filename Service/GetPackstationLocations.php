<?php

namespace MageSuite\PackstationDhl\Service;

class GetPackstationLocations
{
    const API_ZIP_FIELD = 'zip';

    /**
     * @var \MageSuite\PackstationDhl\Service\Soap
     */
    protected $soap;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \MageSuite\PackstationDhl\Service\Soap $soap,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->soap = $soap;
        $this->logger = $logger;
    }

    public function execute(string $zip)
    {
        if (!is_numeric($zip) || strlen($zip) < 2 || strlen($zip) > 5) {
            return null;
        }

        $client = $this->soap->getClient();

        if (empty($client)) {
            return null;
        }

        $call = $this->prepareCallParameter($zip);

        try {
            $response = $client->getPackstationsByAddress($call);

            return $response->packstation;
        } catch (\Exception $e) {
            $this->logger->error('DHL Packstation resolver error: ' . $e->getMessage());
        }

        return null;
    }

    public function prepareCallParameter($zip)
    {
        $call = new \stdClass();

        $call->key = '';
        $call->address = [self::API_ZIP_FIELD => $zip];

        return $call;
    }
}
