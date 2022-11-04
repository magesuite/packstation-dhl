<?php

namespace MageSuite\PackstationDhl\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_CONFIGURATION = 'carriers/dhl_packstation';
    const DHL_CUSTOMER_NUMBER = 'dhl_customer_number';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected \Magento\Framework\Encryption\EncryptorInterface $encryptor;

    protected ?\Magento\Framework\DataObject $config = null;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        parent::__construct($context);

        $this->scopeConfig = $scopeConfigInterface;
        $this->encryptor = $encryptor;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->getConfig()->getActive();
    }

    public function getMode(): string
    {
        return $this->getConfig()->getMode();
    }

    public function getApiKey(): string
    {
        return $this->getConfig()->getApiKey();
    }

    public function isDebugEnabled(): bool
    {
        return (bool) $this->getConfig()->getDebug();
    }

    protected function getConfig():? \Magento\Framework\DataObject
    {
        if ($this->config === null) {
            $config = $this->scopeConfig->getValue(self::XML_PATH_CONFIGURATION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $this->config = new \Magento\Framework\DataObject($config ?? []);
        }

        return $this->config;
    }
}
