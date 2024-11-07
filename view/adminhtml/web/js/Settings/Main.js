define([
    'TikTokShop/Common'
], function () {
    window.TikTokShopSettingsMain = Class.create(Common, {

        initialize: function () {
            var self = this

            jQuery.validator.addMethod('validator-required-when-visible', function (value, el) {
                return value > 0
            }, 'This is a required field.')

            const weightValidator = function (value, el) {
                if (self.isElementHiddenFromPage(el)) {
                    return true;
                }

                if (typeof value === 'string') {
                    value = value.trim();
                }

                return new RegExp(/^(?:[0-9]*[.])?[0-9]+$/).test(value);
            }

            const sizeValidator = function (value, el) {
                if (self.isElementHiddenFromPage(el)) {
                    return true;
                }

                if (typeof value === 'string') {
                    value = value.trim();
                }

                return new RegExp(/^[0-9]+$/).test(value);
            }

            jQuery.validator.addMethod('validator-tts-weight', weightValidator, TikTokShop.translator.translate('not_valid_weight_message'))
            jQuery.validator.addMethod('validator-tts-length', sizeValidator, TikTokShop.translator.translate('not_valid_length_message'))
            jQuery.validator.addMethod('validator-tts-width', sizeValidator, TikTokShop.translator.translate('not_valid_width_message'))
            jQuery.validator.addMethod('validator-tts-height', sizeValidator, TikTokShop.translator.translate('not_valid_height_message'))

            this.initObservers()
        },

        initObservers: function () {
            var self = this;

            $('identifier_code_mode').addEventListener('change', function () {
                self.identifier_code_mode_change(this)
            });

            $('package_weight_mode').addEventListener('change', function () {
                self.package_mode_change(this, $('package_weight_custom_attribute'), $('package_weight_custom_value'))
            });

            $('package_length_mode').addEventListener('change', function () {
                self.package_mode_change(this, $('package_length_custom_attribute'), $('package_length_custom_value'))
            });

            $('package_width_mode').addEventListener('change', function () {
                self.package_mode_change(this, $('package_width_custom_attribute'), $('package_width_custom_value'))
            });

            $('package_height_mode').addEventListener('change', function () {
                self.package_mode_change(this, $('package_height_custom_attribute'), $('package_height_custom_value'))
            });
        },

        // ---------------------------------------

        identifier_code_mode_change: function (option) {
            var self = TikTokShopSettingsMainObj;

            $('identifier_code_custom_attribute').value = '';
            if (option.value == TikTokShop.php.constant('M2E_TikTokShop_Helper_Component_TikTokShop_Configuration::IDENTIFIER_CODE_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(option, $('identifier_code_custom_attribute'));
            }
        },

        package_mode_change: function (option, customAttributeInput, customValueInput) {
            customAttributeInput.value = ''
            customValueInput.value = ''
            customValueInput.hide()

            if (option.value == TikTokShop.php.constant('M2E_TikTokShop_Helper_Component_TikTokShop_Configuration::PACKAGE_MODE_CUSTOM_ATTRIBUTE')) {
                this.updateHiddenValue(option, customAttributeInput);
            }

            if (option.value == TikTokShop.php.constant('M2E_TikTokShop_Helper_Component_TikTokShop_Configuration::PACKAGE_MODE_CUSTOM_VALUE')) {
                customValueInput.show();
            }
        },
    })
})
