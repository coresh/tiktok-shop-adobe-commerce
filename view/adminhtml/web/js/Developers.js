define([
    'TikTokShop/Plugin/Messages'
], function (MessagesObj) {
    window.Developers = Class.create({

        inspectionModeElement: null,
        inspectionModeValue: null,

        // ---------------------------------------

        initialize: function () {
            var self = this;

            self.inspectionModeElement = $('listing_product_inspector_mode');
            self.inspectionModeValue = self.inspectionModeElement.value;

            self.inspectionModeElement.observe('change', function () {
                var currentValue = self.inspectionModeElement.value;

                if (currentValue != self.inspectionModeValue) {
                    $('save_inspector_mode').show();
                } else {
                    $('save_inspector_mode').hide();
                }
            });
        },

        // ---------------------------------------

        saveDirectDatabaseChanges: function () {
            var self = this;

            new Ajax.Request(TikTokShop.url.get('developers/save'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    listing_product_inspector_mode: $('listing_product_inspector_mode').value
                },
                onSuccess: function (transport) {
                    var result = transport.responseText;

                    MessagesObj.clear();
                    if (!result.isJSON()) {
                        MessagesObj.addError(result);
                    }

                    result = JSON.parse(result);

                    if (result.success) {
                        MessagesObj.addSuccess(TikTokShop.translator.translate('Settings saved'));
                        $('save_inspector_mode').hide();
                        self.inspectionModeValue = self.inspectionModeElement.value;
                    } else {
                        MessagesObj.addError(TikTokShop.translator.translate('Error'));
                    }
                }
            });
        }

        // ---------------------------------------
    });
});
