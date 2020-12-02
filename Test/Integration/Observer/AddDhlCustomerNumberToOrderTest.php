<?php
namespace MageSuite\PackstationDhl\Test\Integration\Observer;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class AddCustomerTypeToOrderTest extends \PHPUnit\Framework\TestCase
{
    const DEFAULT_STORE_ID = 1;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->cartManagement = $this->objectManager->get(\Magento\Quote\Api\CartManagementInterface::class);
        $this->cartRepository = $this->objectManager->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->orderRepository = $this->objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     */
    public function testItAddsDhlCustomerNumberCorrectlyToOrder()
    {
        $dhlCustomerNumber = 321;

        $qty = 1;
        $product = $this->productRepository->get('product');

        $quote = $this->prepareQuote($product, $qty, $dhlCustomerNumber);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        $order = $this->orderRepository->get($orderId);

        $this->assertEquals($dhlCustomerNumber, $order->getData(\MageSuite\PackstationDhl\Helper\Configuration::DHL_CUSTOMER_NUMBER));
    }

    private function prepareQuote($product, $qty, $dhlCustomerNumber)
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

    public static function loadProducts()
    {
        require __DIR__ . '/../../_files/product.php';
    }

    public static function loadProductsRollback()
    {
        require __DIR__ . '/../../_files/product_rollback.php';
    }
}
