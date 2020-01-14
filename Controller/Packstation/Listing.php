<?php

namespace MageSuite\PackstationDhl\Controller\Packstation;

class Listing extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \MageSuite\PackstationDhl\Service\GetPackstationLocations
     */
    protected $getPackstationLocations;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \MageSuite\PackstationDhl\Service\GetPackstationLocations $getPackstationLocations
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->getPackstationLocations = $getPackstationLocations;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $zip = $this->getRequest()->getParam(\MageSuite\PackstationDhl\Service\GetPackstationLocations::API_ZIP_FIELD, null);
        $response = $this->getPackstationLocations->execute($zip);

        return $resultJson->setData($response);
    }
}
