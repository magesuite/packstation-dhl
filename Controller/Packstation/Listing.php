<?php

namespace MageSuite\PackstationDhl\Controller\Packstation;

class Listing extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    const FORM_ZIP_FIELD = 'zip';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \MageSuite\PackstationDhl\Service\GetPackstationLocations
     */
    protected $getPackstationLocations;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $pageCacheConfig;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \MageSuite\PackstationDhl\Service\GetPackstationLocations $getPackstationLocations,
        \Magento\PageCache\Model\Config $pageCacheConfig
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->getPackstationLocations = $getPackstationLocations;
        $this->pageCacheConfig = $pageCacheConfig;
    }

    public function execute()
    {
        $zip = (string)$this->getRequest()->getParam(self::FORM_ZIP_FIELD);
        $resultJson = $this->resultJsonFactory->create();
        $response = $this->getPackstationLocations->execute($zip);

        if (!empty($response)) {
            $this->getResponse()->setPublicHeaders($this->pageCacheConfig->getTtl());
        }

        return $resultJson->setData($response);
    }
}
