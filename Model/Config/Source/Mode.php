<?php
namespace MageSuite\PackstationDhl\Model\Config\Source;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    const MODE_LIVE = 'live';
    const MODE_SANDBOX = 'sandbox';

    public function toOptionArray()
    {
        return [
            self::MODE_LIVE => __('Live'),
            self::MODE_SANDBOX => __('Sandbox')
        ];
    }
}
