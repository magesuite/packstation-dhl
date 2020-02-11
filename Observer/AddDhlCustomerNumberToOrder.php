<?php
namespace MageSuite\PackstationDhl\Observer;

class AddDhlCustomerNumberToOrder implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();

        $dhlCustomerNumber = $quote->getData(\MageSuite\PackstationDhl\Helper\Configuration::DHL_CUSTOMER_NUMBER);

        if (empty($dhlCustomerNumber)) {
            return $this;
        }

        $order = $observer->getOrder();
        $order->setData(\MageSuite\PackstationDhl\Helper\Configuration::DHL_CUSTOMER_NUMBER, $dhlCustomerNumber);

        return $this;
    }
}
