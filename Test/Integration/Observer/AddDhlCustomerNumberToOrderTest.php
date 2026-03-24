<?php

declare(strict_types=1);

namespace MageSuite\PackstationDhl\Test\Integration\Observer;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class AddDhlCustomerNumberToOrderTest extends \PHPUnit\Framework\TestCase
{
    protected const DEFAULT_STORE_ID = 1;

    protected ?\Magento\TestFramework\ObjectManager $objectManager = null;
    protected ?\Magento\Store\Model\StoreManagerInterface $storeManager = null;
    protected ?\Magento\Quote\Api\CartManagementInterface $cartManagement = null;
    protected ?\Magento\Quote\Api\CartRepositoryInterface $cartRepository = null;
    protected ?\Magento\Quote\Model\QuoteManagement $quoteManagement = null;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository = null;
    protected ?\Magento\Sales\Api\OrderRepositoryInterface $orderRepository = null;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->cartManagement = $this->objectManager->get(\Magento\Quote\Api\CartManagementInterface::class);
        $this->cartRepository = $this->objectManager->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->orderRepository = $this->objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $this->quoteManagement = $this->objectManager->get(\Magento\Quote\Model\QuoteManagement::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_PackstationDhl::Test/_files/product.php
     */
    public function testItAddsDhlCustomerNumberCorrectlyToOrder(): void
    {
        $dhlCustomerNumber = 321;

        $qty = 1;
        $product = $this->productRepository->get('product');

        $quote = $this->prepareQuote($product, $qty, $dhlCustomerNumber);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        $order = $this->orderRepository->get($orderId);

        $this->assertEquals($dhlCustomerNumber, $order->getData(\MageSuite\PackstationDhl\Helper\Configuration::DHL_CUSTOMER_NUMBER));
    }

    protected function prepareQuote(\Magento\Catalog\Api\Data\ProductInterface $product, int $qty, int $dhlCustomerNumber): \Magento\Quote\Model\Quote
    {
        $addressData = [
            'region' => 'BE',
            'postcode' => '11111',
            'lastname' => 'lastname',
            'firstname' => 'firstname',
            'street' => 'street',
            'city' => 'Los Angeles',
            'email' => 'admin@example.com',
            'telephone' => '11111111',
            'country_id' => 'DE'
        ];

        $shippingMethod = 'freeshipping_freeshipping';

        $store = $this->storeManager->getStore(self::DEFAULT_STORE_ID);

        $cartId = $this->cartManagement->createEmptyCart();
        $quote = $this->cartRepository->get($cartId);
        $quote->setStore($store);

        $quote->setCustomerEmail('test@example.com');
        $quote->setCustomerIsGuest(true);

        $quote->setCurrency();

        $quote->addProduct($product, intval($qty));

        $billingAddress = $this->objectManager->create('Magento\Quote\Api\Data\AddressInterface', ['data' => $addressData]);
        $billingAddress->setAddressType('billing');

        $shippingAddress = clone $billingAddress;
        $shippingAddress->setId(null)->setAddressType('shipping');

        $rate = $this->objectManager->create(\Magento\Quote\Model\Quote\Address\Rate::class);
        $rate->setCode($shippingMethod);

        $quote->getPayment()->importData(['method' => 'checkmo']);

        $quote->setBillingAddress($billingAddress);
        $quote->setShippingAddress($shippingAddress);
        $quote->getShippingAddress()->addShippingRate($rate);
        $quote->getShippingAddress()->setShippingMethod($shippingMethod);

        $quote->setPaymentMethod('checkmo');
        $quote->setInventoryProcessed(false);

        $quote->save();

        $quote->collectTotals();

        $quote->setData(\MageSuite\PackstationDhl\Helper\Configuration::DHL_CUSTOMER_NUMBER, $dhlCustomerNumber);

        return $quote;
    }
}
