define(['underscore', 'Magento_Checkout/js/model/quote'], function(_, quote) {
    'use strict';

    return function(selectShippingAddress) {
        return function(shippingAddress) {
            if (
                _.isMatch(quote.shippingMethod(), {
                    carrier_code: 'dhl_packstation',
                    method_code: 'dhl_packstation',
                }) &&
                shippingAddress.getType() !== 'packstation-address'
            ) {
                return;
            }

            selectShippingAddress(shippingAddress);
        };
    };
});
