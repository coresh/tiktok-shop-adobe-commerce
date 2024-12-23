define([
    'uiClass',
    'jquery',
    'mage/translate',
], (Class, $, $t) => {
    'use strict';

    var ListingQuality = Class.extend({

        observedSelector: '[data-has-recommendations]',

        initialize: function () {
            this._super().initObservers();

            return this;
        },

        initObservers: function () {
            $(document).on('click', this.observedSelector, this.getRecomendattion.bind(this));
        },

        /**
         * @param jQuery.Event event
         */
        getRecomendattion: function (event) {
            const target = $(event.currentTarget)

            new Ajax.Request(TikTokShop.url.get('getListingQualityRecommendation'), {
                method: 'get',
                parameters: {
                    product_id: target.attr('data-product-id')
                },
                asynchronous: true,
                onSuccess: this.openPopup
            });
        },

        openPopup: function (transport) {
            const content = transport.responseText;
            if (content.trim().length === 0) {
                return;
            }

            $('<div />').html(content).modal({
                title: $t('Listing Quality Recommendation'),
                modalClass: 'tts-listing-quality-recommendation-pop-up width-50',
                buttons: [{
                    class: 'primary',
                    text: $t('Close'),
                    click: function () {
                        this.closeModal();
                    }
                }]
            }).trigger('openModal')
        }
    })

    return new ListingQuality;
});

