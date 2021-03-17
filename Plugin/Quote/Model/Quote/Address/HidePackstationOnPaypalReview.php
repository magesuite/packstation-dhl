<?php

namespace MageSuite\PackstationDhl\Plugin\Quote\Model\Quote\Address;

class HidePackstationOnPaypalReview
{
    /**
     * @var \MageSuite\PackstationDhl\Model\Carrier\PackstationDhl
     */
    protected $packstationDhl;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var array
     */
    protected $forbiddenActionList = [];

    public function __construct(
        \MageSuite\PackstationDhl\Model\Carrier\PackstationDhl $packstationDhl,
        \Magento\Framework\App\Request\Http $request,
        array $forbiddenActionList = []
    ) {
        $this->packstationDhl = $packstationDhl;
        $this->request = $request;
        $this->forbiddenActionList = $forbiddenActionList;
    }

    public function afterGetGroupedAllShippingRates(\Magento\Quote\Model\Quote\Address $subject, $result)
    {
        if (!$this->isActionNameForbidden()) {
            return $result;
        }

        $carrierCode = $this->packstationDhl->getCarrierCode();

        if (isset($result[$carrierCode])) {
            unset($result[$carrierCode]);
        }

        return $result;
    }

    protected function isActionNameForbidden(): bool
    {
        $actionName = $this->request->getFullActionName();

        if (empty($this->forbiddenActionList)) {
            return false;
        }

        return in_array($actionName, $this->forbiddenActionList);
    }
}
