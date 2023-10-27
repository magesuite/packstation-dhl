<?php

declare(strict_types=1);

namespace MageSuite\PackstationDhl\Service;

class DhlApiClient
{
    public const VALUE_MODE_LIVE = 'live';

    public const API_ENDPOINT_SANDBOX = 'https://api-sandbox.dhl.com/location-finder/v1';
    public const API_ENDPOINT_LIVE = 'https://api.dhl.com/location-finder/v1';
    public const API_METHOD_FIND_BY_ADDRESS = 'find-by-address';

    public const TIMEOUT = 30;
    public const HTTP_OK = 200;

    protected \MageSuite\PackstationDhl\Helper\Configuration $configuration;
    protected \Magento\Framework\HTTP\Client\Curl $curl;
    protected \Magento\Framework\Serialize\SerializerInterface $serializer;
    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(
        \MageSuite\PackstationDhl\Helper\Configuration $configuration,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->configuration = $configuration;
        $this->curl = $curl;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * @throws \MageSuite\PackstationDhl\Exception\ApiException
     */
    public function getPackstationsByAddress($data): array
    {
        return $this->sendRequest(self::API_METHOD_FIND_BY_ADDRESS, $data);
    }

    /**
     * @throws \MageSuite\PackstationDhl\Exception\ApiException
     */
    protected function sendRequest($method, $data): array
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
            throw new \MageSuite\PackstationDhl\Exception\ApiException($result);
        }

        return $this->serializer->unserialize($result);
    }

    protected function getUri($method, $data): string
    {
        return sprintf(
            '%s/%s?%s',
            $this->getApiEndpoint(),
            $method,
            http_build_query($data)
        );
    }

    protected function getApiEndpoint(): string
    {
        $mode = $this->configuration->getMode();

        return $mode == self::VALUE_MODE_LIVE ? self::API_ENDPOINT_LIVE : self::API_ENDPOINT_SANDBOX;
    }

    protected function logRequest($method, $data, $status, $result): void
    {
        $this->logger->info(sprintf('DHL API request method %s with data: %s, returned status %s and result: %s', $method, json_encode($data), $status, $result));
    }
}
