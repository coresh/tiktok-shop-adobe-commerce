define([
    'Magento_Ui/js/modal/modal',
    'TikTokShop/Listing/MovingFromListing',
    'TikTokShop/Listing/SellOnAnotherMarket',
    'TikTokShop/TikTokShop/Listing/View/Grid'
], function (modal) {

    window.TikTokShopListingViewTikTokShopGrid = Class.create(TikTokShopListingViewGrid, {

        // ---------------------------------------

        afterInitPage: function ($super) {
            $super();

            $(this.gridId + '_massaction-select').observe('change', function () {
                if (!$('get-estimated-fee')) {
                    return;
                }

                if (this.value == 'list') {
                    $('get-estimated-fee').show();
                } else {
                    $('get-estimated-fee').hide();
                }
            });
        },

        // ---------------------------------------

        prepareActions: function ($super) {
            $super();

            this.movingHandler = new MovingFromListing(this);
            const sellOnAnotherMarketActionHandler = new SellOnAnotherMarketAction(this)

            this.actions = Object.extend(this.actions, {
                movingAction: this.movingHandler.run.bind(this.movingHandler),
                sellOnAnotherMarketAction: sellOnAnotherMarketActionHandler.handle.bind(sellOnAnotherMarketActionHandler)
            });
        },

        // ---------------------------------------

        tryToMove: function (listingId) {
            this.movingHandler.submit(listingId, this.onSuccess)
        },

        onSuccess: function () {
            this.unselectAllAndReload();
        },

        // ---------------------------------------

        getMaxProductsInPart: function () {
            return 10;
        },

        // ---------------------------------------

        getLogViewUrl: function (rowId) {
            var idField = TikTokShop.php.constant('\\M2E\\TikTokShop\\Block\\Adminhtml\\Log\\Listing\\Product\\AbstractGrid::LISTING_PRODUCT_ID_FIELD');

            var params = {};
            params[idField] = rowId;

            return TikTokShop.url.get('tiktokshop_log_listing_product/index', params);
        },

        // ---------------------------------------

        openFeePopUp: function (content, title) {
            var feePopup = $('fee_popup');

            if (feePopup) {
                feePopup.remove();
            }

            $('html-body').insert({bottom: '<div id="fee_popup"></div>'});

            $('fee_popup').update(content);

            var popup = jQuery('#fee_popup');

            modal({
                title: title,
                type: 'popup',
                buttons: [{
                    text: TikTokShop.translator.translate('Close'),
                    class: 'action-secondary',
                    click: function () {
                        popup.modal('closeModal');
                    }
                }]
            }, popup);

            popup.modal('openModal');
        },

        // ---------------------------------------
    });
});
