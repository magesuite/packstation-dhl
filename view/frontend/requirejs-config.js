var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/select-shipping-address': {
                'MageSuite_PackstationDhl/js/action/select-shipping-address-ext': true,
            },
            'Magento_Checkout/js/model/quote': {
                'MageSuite_PackstationDhl/js/model/quote-ext': true,
            },
            'Magento_Checkout/js/view/shipping-information': {
                'MageSuite_PackstationDhl/js/view/shipping-information-ext': true,
            },
        },
    },
};
