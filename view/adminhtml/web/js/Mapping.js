define([
    'jquery',
    'mage/translate',
    'M2ECore/Plugin/Messages',
    'TikTokShop/Common',
    'Magento_Ui/js/modal/modal'
], function (jQuery, $t, MessagesObj) {
    window.Mapping = Class.create(Common, {

        // ---------------------------------------

        initialize: function () {

            this.messageObj = Object.create(MessagesObj);
            this.messageObj.setContainer('#anchor-content');


            this.initFormValidation();
        },

        // ---------------------------------------

        saveSettings: function () {
            let isFormValid = true;
            let uiTabs = jQuery.find('div.ui-tabs-panel')
            uiTabs.forEach(item => {
                let elementId = item.getAttribute('data-ui-id').split('-').pop();
                if (isFormValid) {
                    let form = jQuery(item).find('form');
                    if (form.length) {
                        if (!form.valid()) {
                            isFormValid = false;
                            return;
                        }

                        if (!TikTokShop.url.urls[elementId]) {
                            return;
                        }

                        jQuery("a[name='" + elementId + "']").removeClass('_changed _error');
                        let formData = form.serialize(true);
                        formData.tab = elementId;
                        this.submitTab(TikTokShop.url.get(elementId), formData);
                    }
                }
            })
        },

        submitTab: function (url, formData) {
            let self = this;

            new Ajax.Request(url, {
                method: 'post',
                asynchronous: false,
                parameters: formData || {},
                onSuccess: function (transport) {
                    let result = transport.responseText;

                    self.messageObj.clear();
                    if (!result.isJSON()) {
                        self.messageObj.addError(result);
                    }

                    result = JSON.parse(result);

                    if (typeof result['view_show_block_notices_mode'] !== 'undefined') {
                        BLOCK_NOTICES_SHOW = result['view_show_block_notices_mode'];
                        BlockNoticeObj.initializedBlocks = [];
                        BlockNoticeObj.init();
                    }

                    if (result.messages && Array.isArray(result.messages) && result.messages.length) {
                        self.scrollPageToTop();
                        result.messages.forEach(function (el) {
                            let key = Object.keys(el).shift();
                            self.messageObj['add' + key.capitalize()](el[key]);
                        });
                        return;
                    }

                    if (result.success) {
                        self.messageObj.addSuccess($t('Settings saved'));
                    } else {
                        self.messageObj.addError($t('Error'));
                    }
                }
            });
        }
    });
});
