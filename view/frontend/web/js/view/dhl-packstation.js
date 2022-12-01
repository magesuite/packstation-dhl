define([
    'uiComponent',
    'underscore',
    'jquery',
    'knockout',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/shipping-rate-service',
    'MageSuite_PackstationDhl/js/model/shipping-rate-processor/packstation-address',
    'MageSuite_PackstationDhl/js/model/packstations-service',
    'Magento_Checkout/js/action/select-shipping-address',
], function(
    Component,
    _,
    $,
    ko,
    registry,
    quote,
    selectShippingMethodAction,
    checkoutData,
    shippingService,
    stepNavigator,
    shippingRateService,
    shippingRateProcessor,
    packstationsService,
    selectShippingAddress
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MageSuite_PackstationDhl/dhl-packstation',
            isVisible: false,
            isAvailable: false,
            isPackstationSelected: false,
            rate: {
                carrier_code: 'dhl_packstation',
                method_code: 'dhl_packstation',
            },
            nearbySearchLimit: 50,
            defaultCountry: window.checkoutConfig.defaultCountryId,
            rates: shippingService.getShippingRates(),
            inStoreMethod: null,
            storedShippingAddress: {}
        },

        /**
         * @inheritdoc
         */
        initialize: function() {
            this._super();

            shippingRateService.registerProcessor(
                'packstation-address',
                shippingRateProcessor
            );

            quote.shippingAddress.subscribe(function(shippingAddress) {
                this.convertAddressType(shippingAddress);
            }, this);
            this.convertAddressType(quote.shippingAddress());

            this.isPackstationSelected.subscribe(function(isSelected) {
                this.preselectLocation();

                if (!isSelected && this.storedShippingAddress) {
                    selectShippingAddress(this.storedShippingAddress);
                } else {
                    if (['customer-address', 'new-customer-address'].includes(quote.shippingAddress().getType())) {
                        this.storedShippingAddress = quote.shippingAddress();
                    }
                }
            }, this);
            this.preselectLocation();

            this.syncWithShipping();
        },

        /**
         * Init component observable variables
         *
         * @return {exports}
         */
        initObservable: function() {
            this._super().observe(['isVisible']);

            this.isPackstationSelected = ko.pureComputed(function() {
                return _.isMatch(quote.shippingMethod(), this.rate);
            }, this);

            this.isAvailable = ko.pureComputed(function() {
                return _.findWhere(this.rates(), {
                    carrier_code: this.rate['carrier_code'],
                    method_code: this.rate['method_code'],
                });
            }, this);

            return this;
        },

        /**
         * Synchronize DHL packstation visibility with shipping step.
         *
         * @returns void
         */
        syncWithShipping: function() {
            var shippingStep = _.findWhere(stepNavigator.steps(), {
                code: 'shipping',
            });

            shippingStep.isVisible.subscribe(function(isShippingVisible) {
                this.isVisible(this.isAvailable && isShippingVisible);
            }, this);
            this.isVisible(this.isAvailable && shippingStep.isVisible());
        },

        /**
         * @returns void
         */
        selectShipping: function() {
            var nonPackstationMethod = _.find(
                this.rates(),
                function(rate) {
                    return (
                        rate['carrier_code'] !== this.rate['carrier_code'] &&
                        rate['method_code'] !== this.rate['method_code']
                    );
                },
                this
            );

            this.selectShippingMethod(nonPackstationMethod);

            registry.async('checkoutProvider')(function(checkoutProvider) {
                checkoutProvider.set(
                    'shippingAddress',
                    quote.shippingAddress()
                );
            });
        },

        /**
         * @returns void
         */
        selectPackstation: function() {
            var packstationShippingMethod = _.findWhere(
                this.rates(),
                {
                    carrier_code: this.rate['carrier_code'],
                    method_code: this.rate['method_code'],
                },
                this
            );

            this.preselectLocation();
            this.selectShippingMethod(packstationShippingMethod);
        },

        /**
         * @param {Object} shippingMethod
         */
        selectShippingMethod: function(shippingMethod) {
            selectShippingMethodAction(shippingMethod);
            checkoutData.setSelectedShippingAddress(
                quote.shippingAddress().getKey()
            );
        },

        /**
         * @param {Object} shippingAddress
         * @returns void
         */
        convertAddressType: function(shippingAddress) {
            if (
                !this.isPackstationAddress(shippingAddress) &&
                this.isPackstationSelected()
            ) {
                quote.shippingAddress(
                    $.extend({}, shippingAddress, {
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
                    })
                );
            }
        },

        /**
         * @returns void
         */
        preselectLocation: function() {
            var selectedPackstation,
                shippingAddress,
                customAttributes,
                selectedPackstationId,
                nearestLocation;

            if (!this.isPackstationSelected()) {
                return;
            }

            selectedPackstation = packstationsService.selectedPackstation();

            if (selectedPackstation) {
                packstationsService.selectForShipping(selectedPackstation);

                return;
            }

            shippingAddress = quote.shippingAddress();
            customAttributes = shippingAddress.customAttributes || [];
            selectedPackstationId = _.findWhere(customAttributes, {
                attribute_code: 'packstation_id',
            });

            // if (selectedPackstationId) {
            //     packstationsService
            //         .getPackstation(selectedPackstationId.value)
            //         .then(function(location) {
            //             packstationsService.selectForShipping(location);
            //         });
            // } else
            if (shippingAddress.postcode) {
                packstationsService
                    .getNearbyPackstations(shippingAddress.postcode)
                    .then(function(locations) {
                        nearestLocation = locations[0];

                        if (nearestLocation) {
                            packstationsService.selectForShipping(
                                nearestLocation
                            );
                        }
                    });
            }
        },

        /**
         * @param {Object} address
         * @returns {Boolean}
         */
        isPackstationAddress: function(address) {
            return address.getType() === 'packstation-address';
        },
    });
});
