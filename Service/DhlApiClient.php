<?php

namespace MageSuite\PackstationDhl\Service;

class DhlApiClient
{
    const API_ENDPOINT_SANDBOX = 'https://api-sandbox.dhl.com/location-finder/v1';

    const API_ENDPOINT_LIVE = 'https://api.dhl.com/location-finder/v1';

    const API_METHOD_FIND_BY_ADDRESS = 'find-by-address';

    const TIMEOUT = 30;

    const HTTP_OK = 200;

    protected \MageSuite\PackstationDhl\Helper\Configuration $configuration;

    protected \Magento\Framework\HTTP\Client\Curl $curl;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(
        \MageSuite\PackstationDhl\Helper\Configuration $configuration,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->configuration = $configuration;
        $this->curl = $curl;
        $this->logger = $logger;
    }

    public function getPackstationsByAddress($data)
    {
        return $this->sendRequest(self::API_METHOD_FIND_BY_ADDRESS, $data);
    }

    protected function sendRequest($method, $data)
    {
        $this->curl->setHeaders([
            'Content-Type' => \Magento\Analytics\Model\Connector\Http\JsonConverter::CONTENT_MEDIA_TYPE,
            'DHL-API-Key' => $this->configuration->getApiKey()
        ]);

        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setTimeout(self::TIMEOUT);
        $this->curl->get($this->getUri($method, $data));

        $status = $this->curl->getStatus();
        $result = $this->curl->getBody();

        if ($this->configuration->isDebugEnabled()) {
            $this->logRequest($method, $data, $status, $result);
        }

        if ($status !== self::HTTP_OK) {
            throw new \Creativestyle\MageSuite\PackstationDhl\Exception\ApiException($result);
        }

        return json_decode($result);
    }

    protected function getUri($method, $data)
    {
        return sprintf(
            '%s/%s?%s',
            $this->getApiEndpoint(),
            $method,
            http_build_query($data)
        );
    }

    protected function getApiEndpoint()
    {
        $mode = $this->configuration->getMode();
        return $mode == 'live' ? self::API_ENDPOINT_LIVE : self::API_ENDPOINT_SANDBOX;
    }

    protected function logRequest($method, $data, $status, $result)
    {
        $this->logger->info(sprintf('DHL API request method %s with data: %s, returned status %s and result: %s', $method, json_encode($data), $status, $result));
    }
}
