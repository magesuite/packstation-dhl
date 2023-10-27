<?php

declare(strict_types=1);

namespace MageSuite\PackstationDhl\Helper;

class Configuration
{
    public const XML_PATH_CARRIERS_DHL_PACKSTATION_ACTIVE = 'carriers/dhl_packstation/active';
    public const XML_PATH_CARRIERS_DHL_PACKSTATION_MODE = 'carriers/dhl_packstation/mode';
    public const XML_PATH_CARRIERS_DHL_PACKSTATION_API_KEY = 'carriers/dhl_packstation/api_key';
    public const XML_PATH_CARRIERS_DHL_PACKSTATION_COUNTRY_CODE = 'carriers/dhl_packstation/country_code';
    public const XML_PATH_CARRIERS_DHL_PACKSTATION_DEBUG = 'carriers/dhl_packstation/debug';

    public const DHL_CUSTOMER_NUMBER = 'dhl_customer_number';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CARRIERS_DHL_PACKSTATION_ACTIVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getMode(?int $storeId = null): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CARRIERS_DHL_PACKSTATION_MODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getApiKey(?int $storeId = null): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CARRIERS_DHL_PACKSTATION_API_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getCountryCode(?int $storeId = null): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CARRIERS_DHL_PACKSTATION_COUNTRY_CODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isDebugEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CARRIERS_DHL_PACKSTATION_DEBUG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}
