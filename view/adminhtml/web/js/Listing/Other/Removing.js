define([
    'TikTokShop/Plugin/Messages',
    'TikTokShop/Action'
], function (MessagesObj) {
    window.ListingOtherRemoving = Class.create(Action, {

        // ---------------------------------------

        run: function () {
            this.removingProducts(
                    this.gridHandler.getSelectedProductsString()
            );
        },

        removingProducts: function (productsString) {
            new Ajax.Request(TikTokShop.url.get('removingProducts'), {
                method: 'post',
                parameters: {
                    product_ids: productsString
                },
                onSuccess: (function (transport) {

                    MessagesObj.clear();

                    if (transport.responseText == '1') {
                        MessagesObj.addSuccess(TikTokShop.translator.translate('Product(s) was Removed.'));
                    } else {
                        MessagesObj.addError(TikTokShop.translator.translate('Not enough data'));
                    }

                    this.gridHandler.unselectAllAndReload();
                }).bind(this)
            });
        }

        // ---------------------------------------
    });
});
