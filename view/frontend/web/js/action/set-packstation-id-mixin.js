define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();

            if (shippingAddress.street &&
                shippingAddress.street[0]) {
                shippingAddress.extension_attributes = $.extend(
                    {},
                    shippingAddress.extension_attributes,
                    {
                        packstation_id: shippingAddress.street[0]
                    }
                );
            }

            return originalAction();
        });
    };
});
