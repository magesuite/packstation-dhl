<?php

declare(strict_types=1);

namespace MageSuite\PackstationDhl\Controller\Packstation;

class Listing implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    protected const FORM_ZIP_FIELD = 'zip';

    public function __construct(
        protected \Magento\Framework\App\Request\Http $request,
        protected \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        protected \Magento\Framework\App\ResponseInterface $response,
        protected \Magento\PageCache\Model\Config $pageCacheConfig,
        protected \MageSuite\PackstationDhl\Service\GetPackstationLocations $getPackstationLocations,
    ) {}

    public function execute(): \Magento\Framework\Controller\Result\Json
    {
        $zip = (string)$this->request->getParam(self::FORM_ZIP_FIELD);
        $resultJson = $this->resultJsonFactory->create();
        $response = $this->getPackstationLocations->execute($zip);

        if (!empty($response)) {
            $this->response->setPublicHeaders($this->pageCacheConfig->getTtl());
        }

        return $resultJson->setData($response);
    }
}
