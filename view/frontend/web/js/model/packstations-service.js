/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'knockout',
    'MageSuite_PackstationDhl/js/model/resource-url-manager',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/select-shipping-address',
    'underscore',
    'mage/url',
    'mage/translate',
], function(
    $,
    ko,
    resourceUrlManager,
    customerData,
    checkoutData,
    addressConverter,
    selectShippingAddressAction,
    _,
    url
) {
    'use strict';

    var countryData = customerData.get('directory-data');

    return {
        isLoading: ko.observable(false),
        selectedPackstation: ko.observable(null),

        /**
         * Get nearby packstations based on given ZIP code.
         *
         * @param {number} zipCode - ZIP code to base search on.
         */
        getNearbyPackstations: function(zipCode) {
            var self = this,
                serviceUrl = resourceUrlManager.getUrlForNearbyPackstations();

            self.isLoading(true);

            return $.get({url: serviceUrl, cache: true}, { zip: zipCode })
                .then(function(results) {
                    if (!Array.isArray(results)) {
                        return [];
                    }

                    return _.map(results, function(result) {
                        return self.formatResult(result);
                    });
                })
                .fail(function(response) {
                    self.processError(response);

                    return [];
                })
                .always(function() {
                    self.isLoading(false);
                });
        },

        /**
         * Select location for shipping.
         *
         * @param {Object} location
         * @returns void
         */
        selectForShipping: function(location) {
            var address = $.extend(
                {},
                addressConverter.formAddressDataToQuoteAddress({
                    firstname: $.mage.__('Packstation'),
                    lastname: $.mage
                        .__('No. %1')
                        .replace('%1', location['packstation_id']),
                    street: location.street,
                    city: location.city,
                    postcode: location.postcode,
                    country: location.country,
                    country_id: location.countryId,
                    telephone: location.telephone,
                    region: location.region,
                    region_id: location.regionId,
                    save_in_address_book: 0,
                }),
                {
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
                    extension_attributes: {
                        packstation_id: location['packstation_id'],
                    },
                }
            );

            this.selectedPackstation(location);
            selectShippingAddressAction(address);
            checkoutData.setSelectedShippingAddress(address.getKey());
        },

        /**
         * Formats result returned by REST endpoint to match checkout address field naming.
         *
         * @param {Object} result - Single result object returned by REST endpoint.
         */
        formatResult: function(result) {
            var place = result.place,
                address = place.address,
                location = result.location,
                geolocation = place.geo,
                countryId = address.countryCode,
                countryName,
                regionId = 0;

            countryName =
                (countryData()[countryId] &&
                    countryData()[countryId].name) ||
                countryId;

            return {
                name: result.name,
                description: place.containedInPlace?.name,
                latitude: geolocation.latitude,
                longitude: geolocation.longitude,
                street: [address.streetAddress],
                city: address.addressLocality,
                postcode: address.postalCode,
                country: countryName,
                countryId: countryId,
                regionId: regionId,
                packstation_id: location.keywordId,
            };
        },

        /**
         * Process response errors.
         *
         * @param {Object} response
         * @returns void
         */
        processError: function(response) {
            var expr = /([%])\w+/g,
                error;

            if (response.status === 401) {
                //eslint-disable-line eqeqeq
                window.location.replace(url.build('customer/account/login/'));

                return;
            }

            try {
                error = JSON.parse(response.responseText);
            } catch (exception) {
                error = $t(
                    'Something went wrong with your request. Please try again later.'
                );
            }

            if (error.hasOwnProperty('parameters')) {
                error = error.message.replace(expr, function(varName) {
                    varName = varName.substr(1);

                    if (error.parameters.hasOwnProperty(varName)) {
                        return error.parameters[varName];
                    }

                    return error.parameters.shift();
                });
            }
        },
    };
});
