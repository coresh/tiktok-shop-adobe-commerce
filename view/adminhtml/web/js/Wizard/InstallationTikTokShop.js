define([
    'TikTokShop/Plugin/Messages',
], function (MessageObj) {

    window.WizardInstallationTikTokShop = Class.create(Common, {

        continueStep: function () {
            if (WizardObj.steps.current.length) {
                this[WizardObj.steps.current + 'Step']();
            }
        },

        // Steps
        // ---------------------------------------

        registrationStep: function () {
            WizardObj.registrationStep(TikTokShop.url.get('wizard_registration/createLicense'));
        },

        accountStep: function () {
            if (!this.isValidForm()) {
                return false;
            }

            new Ajax.Request(TikTokShop.url.get('wizard_installationTikTokShop/beforeToken'), {
                method: 'post',
                asynchronous: true,
                parameters: $('edit_form').serialize(),
                onSuccess: function (transport) {

                    var response = transport.responseText.evalJSON();

                    if (response && response['message']) {
                        MessageObj.addError(response['message']);
                        return CommonObj.scrollPageToTop();
                    }

                    if (!response['url']) {
                        MessageObj.addError(TikTokShop.translator.translate('An error during of account creation.'));
                        return CommonObj.scrollPageToTop();
                    }

                    return setLocation(response['url']);
                }
            });
        },

        settingsStep: function () {
            this.initFormValidation();

            if (!this.isValidForm()) {
                return false;
            }

            this.submitForm(TikTokShop.url.get('wizard_installationTikTokShop/settingsContinue'));
        },

        listingTutorialStep: function () {
            WizardObj.setStep(WizardObj.getNextStep(), function () {
                WizardObj.complete();
            });
        }

        // ---------------------------------------
    });
});
