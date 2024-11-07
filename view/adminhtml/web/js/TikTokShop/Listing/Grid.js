define([
    'TikTokShop/Grid',
    'prototype'
], function () {

    window.TikTokShopListingGrid = Class.create(Grid, {

        // ---------------------------------------

        backParam: base64_encode('*/tiktokshop_listing/index'),

        // ---------------------------------------

        prepareActions: function () {
            return false;
        },

        // ---------------------------------------

        addProductsSourceProductsAction: function (id) {
            setLocation(TikTokShop.url.get('tiktokshop_listing_product_add/index', {
                id: id,
                source: 'product',
                clear: true,
                back: this.backParam
            }));
        },

        // ---------------------------------------

        addProductsSourceCategoriesAction: function (id) {
            setLocation(TikTokShop.url.get('tiktokshop_listing_product_add/index', {
                id: id,
                source: 'category',
                clear: true,
                back: this.backParam
            }));
        }

        // ---------------------------------------
    });

});
