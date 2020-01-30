define([
    'jquery',
    'underscore',
    'uiComponent',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/set-shipping-information',
    'MageSuite_PackstationDhl/js/model/packstations-service',
    'Magento_Checkout/js/checkout-data',
], function(
    $,
    _,
    Component,
    modal,
    quote,
    customer,
    stepNavigator,
    addressConverter,
    setShippingInformationAction,
    packstationsService,
    checkoutData
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MageSuite_PackstationDhl/packstation-selector',
            selectedPackstationTemplate:
                'MageSuite_PackstationDhl/packstation-selector/selected-location',
            packstationSelectorPopupTemplate:
                'MageSuite_PackstationDhl/packstation-selector/popup',
            packstationSelectorPopupItemTemplate:
                'MageSuite_PackstationDhl/packstation-selector/popup-item',
            loginFormSelector:
                '#packstation-selector form[data-role=email-with-possible-login]',
            addressFormSelector: 'co-packstation-address-form',
            defaultCountryId: window.checkoutConfig.defaultCountryId,
            selectedPackstation: packstationsService.selectedPackstation,
            quoteIsVirtual: quote.isVirtual,
            searchQuery: '',
            nearbyPackstations: null,
            isLoading: packstationsService.isLoading,
            popup: null,
            searchDebounceTimeout: 300,
            imports: {
                nearbySearchRadius: '${ $.parentName }:nearbySearchRadius',
                nearbySearchLimit: '${ $.parentName }:nearbySearchLimit',
            },
        },

        /**
         * Init component
         *
         * @return {exports}
         */
        initialize: function() {
            var updateNearbyPackstations, postcode, city;

            this._super();

            updateNearbyPackstations = _.debounce(function(searchQuery) {
                postcode = null;
                city = searchQuery.replace(/(\d+[\-]?\d+)/, function(match) {
                    postcode = match;

                    return '';
                });

                this.updateNearbyPackstations(
                    addressConverter.formAddressDataToQuoteAddress({
                        city: city,
                        postcode: postcode,
                        country_id: quote.shippingAddress().countryId,
                    })
                );
            }, this.searchDebounceTimeout).bind(this);
            this.searchQuery.subscribe(updateNearbyPackstations);

            return this;
        },

        /**
         * Init component observable variables
         *
         * @return {exports}
         */
        initObservable: function() {
            return this._super().observe(['nearbyPackstations', 'searchQuery']);
        },

        /**
         * Set shipping information handler
         */
        setPackstationInformation: function() {
            var shippingAddress = quote.shippingAddress();

            if (this.validatePackstationInformation()) {
                shippingAddress = addressConverter.quoteAddressToFormAddressData(
                    shippingAddress
                );

                $.extend(
                    shippingAddress.extension_attributes,
                    this.source.get('packstationAddress')
                );

                checkoutData.setShippingAddressFromData(shippingAddress);
                setShippingInformationAction().done(function() {
                    stepNavigator.next();
                });
            }
        },

        /**
         * @return {*}
         */
        getPopup: function() {
            if (!this.popup) {
                this.popup = modal(
                    this.popUpList.options,
                    $(this.popUpList.element)
                );
            }

            return this.popup;
        },

        /**
         * @returns void
         */
        openPopup: function() {
            var shippingAddress = quote.shippingAddress();

            this.getPopup().openModal();

            if (shippingAddress.city && shippingAddress.postcode) {
                this.updateNearbyPackstations(shippingAddress);
            }
        },

        /**
         * @param {Object} location
         * @returns void
         */
        selectPackstation: function(location) {
            packstationsService.selectForShipping(location);
            this.getPopup().closeModal();
        },

        /**
         * @param {Object} location
         * @returns {*|Boolean}
         */
        isPackstationSelected: function(location) {
            return _.isEqual(this.selectedPackstation(), location);
        },

        /**
         * @param {Object} address
         * @returns {*}
         */
        updateNearbyPackstations: function(address) {
            var self = this;

            return packstationsService
                .getNearbyPackstations(address.postcode)
                .then(function(locations) {
                    self.nearbyPackstations(locations);
                })
                .fail(function() {
                    self.nearbyPackstations([]);
                });
        },

        /**
         * @returns {Boolean}
         */
        validatePackstationInformation: function() {
            var emailValidationResult,
                loginFormSelector = this.loginFormSelector;

            this.source.set('params.invalid', false);
            this.source.trigger('packstationAddress.data.validate');

            if (this.source.get('params.invalid')) {
                return false;
            }

            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = $(
                    loginFormSelector + ' input[name=username]'
                ).valid()
                    ? true
                    : false;

                if (!emailValidationResult) {
                    $(this.loginFormSelector + ' input[name=username]').focus();

                    return false;
                }
            }

            return true;
        },
    });
});
