<?php

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

    protected \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory;
    protected \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
    }

    public function getAllowedMethods(): array
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
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

    protected function getShippingPrice(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $shippingPrice = $this->getConfigData('price');
        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);

        if ($shippingPrice === false) {
            $shippingPrice = '0.00';
        }

        $minimumSubtotal = (float)$this->getConfigData('minimum_subtotal_for_free_shipping');
        $orderSubtotal = (float)$request->getData('base_subtotal_incl_tax');

        if ($minimumSubtotal && $orderSubtotal >= $minimumSubtotal) {
            $shippingPrice = 0;
        }

        return $shippingPrice;
    }

    protected function createResultMethod($shippingPrice)
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
