<?php

namespace MageSuite\PackstationDhl\Plugin\Checkout\Model\ShippingInformationManagement;

class SaveDhlCustomerNumberInQuote
{
    protected \Magento\Quote\Api\CartRepositoryInterface $quoteRepository;

    public function __construct(\Magento\Quote\Api\CartRepositoryInterface $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ): array {
        $shippingAddress = $addressInformation->getShippingAddress();

        if (empty($shippingAddress->getExtensionAttributes())) {
            return [$cartId, $addressInformation];
        }

        $dhlCustomerNumber = $shippingAddress->getExtensionAttributes()->getDhlCustomerNumber();

        if (empty($dhlCustomerNumber)) {
            return [$cartId, $addressInformation];
        }

        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setData(\MageSuite\PackstationDhl\Helper\Configuration::DHL_CUSTOMER_NUMBER, $dhlCustomerNumber);

        return [$cartId, $addressInformation];
    }
}
