/**
 * Adjust shipping method title, when the chosen shipping method is Packstation
 */

define(['Magento_Checkout/js/model/quote'], function(quote) {
    'use strict';

    var packstationShippingInformation = {
        defaults: {
            template: 'MageSuite_PackstationDhl/shipping-information',
        },

        /**
         * Get shipping method title based on delivery method.
         *
         * @return {String}
         */
        getShippingMethodTitle: function() {
            var shippingMethod = quote.shippingMethod(),
                locationName = '',
                title;

            if (!this.isPackstation()) {
                return this._super();
            }

            title = `${shippingMethod['carrier_title'] ?? ''} - ${shippingMethod['method_title'] ?? ''}`;

            if (quote.shippingAddress().firstname !== undefined) {
                locationName =
                    quote.shippingAddress().firstname +
                    ' ' +
                    quote.shippingAddress().lastname;
                title += ' "' + locationName + '"';
            }

            return title;
        },

        /**
         * Tells if packstation delivery method selected.
         *
         * @returns {Boolean}
         */
        isPackstation: function() {
            var shippingMethod = quote.shippingMethod(),
                isPackstation = false;

            if (shippingMethod !== null) {
                isPackstation =
                    shippingMethod['carrier_code'] === 'dhl_packstation' &&
                    shippingMethod['method_code'] === 'dhl_packstation';
            }

            return isPackstation;
        },
    };

    return function(shippingInformation) {
        return shippingInformation.extend(packstationShippingInformation);
    };
});
