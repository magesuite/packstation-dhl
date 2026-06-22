<?php

declare(strict_types=1);

namespace MageSuite\PackstationDhl\Model\Carrier;

class PackstationDhl extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'dhl_packstation';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        protected \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        protected \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        protected \Magento\Framework\App\State $state,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function getAllowedMethods(): array
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request): mixed
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if ($this->state->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML) {
            return false;
        }

        $result = $this->rateResultFactory->create();
        $shippingPrice = $this->getShippingPrice($request);

        if ($shippingPrice !== false) {
            $method = $this->createResultMethod($shippingPrice);
            $result->append($method);
        }

        return $result;
    }

    protected function getShippingPrice(\Magento\Quote\Model\Quote\Address\RateRequest $request): float
    {
        $shippingPrice = (float)$this->getConfigData('price');
        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);
        $minimumSubtotal = (float)$this->getConfigData('minimum_subtotal_for_free_shipping');
        $orderSubtotal = (float)$request->getData('base_subtotal_incl_tax');

        if ($minimumSubtotal && $orderSubtotal >= $minimumSubtotal) {
            $shippingPrice = 0.00;
        }

        return $shippingPrice;
    }

    protected function createResultMethod($shippingPrice): \Magento\Quote\Model\Quote\Address\RateResult\Method
    {
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        return $method;
    }
}
