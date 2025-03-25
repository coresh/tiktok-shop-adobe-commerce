define([
    'jquery',
    'M2ECore/Plugin/Messages',
    'TikTokShop/Common'
], function($, messages) {
    window.ManufacturerConfigurationFormAction = Class.create(Common, {
        formMessages: null,

        initialize: function () {
            this.formMessages = Object.create(messages);
        },
        saveAndCloseClick: function (url) {
            if (!this.isValidForm()) {
                return;
            }

            new Ajax.Request(url, {
                method: 'post',
                parameters: $('#edit_form').serialize(),
                onSuccess: (transport) => {
                    const response = JSON.parse(transport.responseText);
                    if (response.status) {
                        window.close();
                        return;
                    }

                    this.formMessages.clear();
                    this.formMessages.addError(response.message)
                },
                onFailure: (transport) => {
                    console.error(transport.responseText)
                }
            });
        },
    });
});
