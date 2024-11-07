define([
    'jquery',
    'TikTokShop/Plugin/Messages',
    'TikTokShop/Action'
], function (jQuery, MessagesObj) {

    window.ListingOtherUnmapping = Class.create(Action, {

        // ---------------------------------------

        run: function () {
            this.unmappingProducts(
                    this.gridHandler.getSelectedProductsString()
            );
        },

        unmappingProducts: function (productsString) {
            new Ajax.Request(TikTokShop.url.get('unmappingProducts'), {
                method: 'post',
                parameters: {
                    product_ids: productsString
                },
                onSuccess: (function (transport) {

                    MessagesObj.clear();

                    if (transport.responseText == '1') {
                        MessagesObj.addSuccess(
                                TikTokShop.translator.translate('Product(s) was Unlinked.')
                        );
                    } else {
                        MessagesObj.addError(
                                TikTokShop.translator.translate('Not enough data')
                        );
                    }

                    this.gridHandler.unselectAllAndReload();
                }).bind(this)
            });
        }

        // ---------------------------------------
    });
});
