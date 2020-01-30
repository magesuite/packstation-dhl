define(['mage/url'], function(url) {
    'use strict';

    return {
        /**
         * Returns URL for REST API to fetch nearby packstations.
         */
        getUrlForNearbyPackstations: function() {
            return url.build('dhl/packstation/listing');
        },
    };
});
