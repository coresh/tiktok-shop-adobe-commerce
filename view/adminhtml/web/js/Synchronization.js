define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'TikTokShop/Plugin/Messages',
    'TikTokShop/Common'
], function (jQuery, modal, MessageObj) {

    window.Synchronization = Class.create(Common, {

        // ---------------------------------------

        saveSettings: function () {
            MessageObj.clear();
            CommonObj.scrollPageToTop();

            new Ajax.Request(TikTokShop.url.get('synch_formSubmit'), {
                method: 'post',
                parameters: {
                    instructions_mode: $('instructions_mode').value
                },
                asynchronous: true,
                onSuccess: function (transport) {
                    MessageObj.addSuccess(TikTokShop.translator.translate('Synchronization Settings have been saved.'));
                }
            });
        }

        // ---------------------------------------
    });
});
