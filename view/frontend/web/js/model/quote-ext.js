/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'MageSuite_PackstationDhl/js/model/packstation-address-converter',
], function(ko, packstationAddressConverter) {
    'use strict';

    return function(quote) {
        var shippingAddress = quote.shippingAddress;

        /**
         * Makes sure that shipping address gets appropriate type when it points
         * to a packstation location.
         */
        quote.shippingAddress = ko.pureComputed({
            /**
             * Return quote shipping address
             */
            read: function() {
                return shippingAddress();
            },

            /**
             * Set quote shipping address
             */
            write: function(address) {
                shippingAddress(
                    packstationAddressConverter.formatAddressToPackstationAddress(
                        address
                    )
                );
            },
        });

        return quote;
    };
});
