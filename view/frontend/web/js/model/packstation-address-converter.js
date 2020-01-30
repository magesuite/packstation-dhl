/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['underscore'], function(_) {
    'use strict';

    return {
        /**
         * Format address to use for packstation
         *
         * @param {Object} address
         * @return {*}
         */
        formatAddressToPackstationAddress: function(address) {
            var sourceCode = _.findWhere(address.customAttributes, {
                attribute_code: 'packstation_id',
            });

            if (sourceCode && address.getType() !== 'packstation-address') {
                address = _.extend({}, address, {
                    saveInAddressBook: 0,

                    /**
                     * Is address can be used for billing
                     *
                     * @return {Boolean}
                     */
                    canUseForBilling: function() {
                        return false;
                    },

                    /**
                     * Returns address type
                     *
                     * @return {String}
                     */
                    getType: function() {
                        return 'packstation-address';
                    },

                    /**
                     * Returns address key
                     *
                     * @return {*}
                     */
                    getKey: function() {
                        return this.getType() + sourceCode.value;
                    },
                });
            }

            return address;
        },
    };
});
