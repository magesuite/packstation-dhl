/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery', 'Magento_Checkout/js/view/form/element/email'], function(
    $,
    Component
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MageSuite_PackstationDhl/form/element/email',
            links: {
                email:
                    'checkout.steps.shipping-step.shippingAddress.customer-email:email',
            },
        },
    });
});
