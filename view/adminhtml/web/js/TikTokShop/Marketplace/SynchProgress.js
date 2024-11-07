define([
    'TikTokShop/Plugin/Messages',
    'TikTokShop/SynchProgress'
], function (MessageObj) {
    window.EbayMarketplaceSynchProgress = Class.create(SynchProgress, {

        // ---------------------------------------

        printFinalMessage: function ($super) {
            new Ajax.Request(TikTokShop.url.get('ebay_marketplace/isExistDeletedCategories'), {
                method: 'post',
                asynchronous: true,
                onSuccess: function (transport) {

                    if (transport.responseText == 1) {
                        MessageObj.addWarning(str_replace(
                                '%url%',
                                TikTokShop.url.get('ebay_category/index', {filter: base64_encode('state=0')}),
                                TikTokShop.translator.translate('Some eBay Categories were deleted from eBay. Click <a target="_blank" href="%url%">here</a> to check.')
                        ));
                    }

                    $super();
                }
            });
        }

        // ---------------------------------------
    });
});
