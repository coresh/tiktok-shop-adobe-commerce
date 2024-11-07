define([
    'Magento_Ui/js/modal/modal',
    'TikTokShop/Common',
    'mage/adminhtml/wysiwyg/tiny_mce/setup'
], function () {
    window.TikTokShopTemplateDescription = Class.create(Common, {

        // ---------------------------------------

        initialize: function () {
            jQuery.validator.addMethod('TikTokShop-validate-description-mode', function (value, el) {

                if (value === '-1') {
                    return false;
                }

                return Validation.get('required-entry').test(value, el);
            }, TikTokShop.translator.translate('This is a required field.'));

            jQuery.validator.addMethod('TikTokShop-validate-description-template', function (value, el) {

                if ($('description_mode').value != TikTokShop.php.constant('\\M2E\\TikTokShop\\Model\\Template\\Description::DESCRIPTION_MODE_CUSTOM')) {
                    return true;
                }

                return Validation.get('required-entry').test(value, el);
            }, TikTokShop.translator.translate('This is a required field.'));


            jQuery.validator.addMethod('TikTokShop-validate-magento-product-id', function (value) {

                var isValidMagentoProductId = false;

                new Ajax.Request(TikTokShop.url.get('tiktokshop_template_description/checkMagentoProductId'), {
                    method: 'post',
                    asynchronous: false,
                    parameters: {
                        product_id: value
                    },
                    onSuccess: function (transport) {
                        var response = transport.responseText.evalJSON();
                        isValidMagentoProductId = response.result;
                    }
                });

                return isValidMagentoProductId;
            }, TikTokShop.translator.translate('Please enter a valid Magento product ID.'));
        },

        initObservers: function () {
            let self = this;

            $('image_main')
                    .observe('change', function () {
                        self.updateHiddenValue(this, $('image_main_attribute'))
                    })
                    .simulate('change');

            $('gallery_images')
                    .observe('change', self.gallery_images_change)
                    .simulate('change');

            $('title_mode')
                    .observe('change', TikTokShopTemplateDescriptionObj.title_mode_change)
                    .simulate('change');

            $('description_mode')
                    .observe('change', TikTokShopTemplateDescriptionObj.description_mode_change)
                    .simulate('change');

            $('custom_inserts_open_popup')
                    .observe('click', TikTokShopTemplateDescriptionObj.customInsertsOpenPopup);


            $('description_template_tr')
                    .down('.admin__field-control')
                    .down('.admin__field')
                    .appendChild($('description_template_buttons'));


            this.initCustomInsertsPopup();
            this.initPreviewPopup();
        },

        // ---------------------------------------

        gallery_images_change: function () {
            let galleryImagesInput = this;
            let galleryImagesInputValue = parseInt(galleryImagesInput.value);
            let attributeCodeInput = $('gallery_images_attribute');
            let imagesLimitInput = $('gallery_images_limit');

            // None
            if (galleryImagesInputValue === 0) {
                attributeCodeInput.value = null;
                imagesLimitInput.value = null;
            }

            // Up to ...
            if (galleryImagesInputValue === 1) {
                attributeCodeInput.value = null;
                imagesLimitInput.value = galleryImagesInput
                        .options[galleryImagesInput.selectedIndex]
                        .getAttribute('attribute_code');
            }

            // Attribute
            if (galleryImagesInputValue === 2) {
                attributeCodeInput.value = galleryImagesInput
                        .options[galleryImagesInput.selectedIndex]
                        .getAttribute('attribute_code');
                imagesLimitInput.value = null;
            }
        },

        title_mode_change: function () {
            var self = TikTokShopTemplateDescriptionObj;
            self.setTextVisibilityMode(this, 'custom_title_tr');
        },

        description_mode_change: function () {
            if (this.value !== '-1' && this.options[0].value === '-1') {
                this.removeChild(this.options[0]);
            }

            var viewEditCustomDescription = $('view_edit_custom_description');

            if (viewEditCustomDescription) {
                viewEditCustomDescription.hide();
            }

            $$('.c-custom_description_tr').invoke('hide');

            if (this.value == TikTokShop.php.constant('\\M2E\\TikTokShop\\Model\\Template\\Description::DESCRIPTION_MODE_CUSTOM')) {
                if (viewEditCustomDescription) {
                    viewEditCustomDescription.show();
                    $$('.c-custom_description_tr').invoke('hide');
                    return;
                }

                $$('.c-custom_description_tr').invoke('show');
            } else {
                if (viewEditCustomDescription) {
                    viewEditCustomDescription.remove();
                }
            }
        },

        view_edit_custom_change: function () {
            $$('.c-custom_description_tr').invoke('show');
            $('view_edit_custom_description').hide();
        },

        setTextVisibilityMode: function (obj, elementName) {
            var elementObj = $(elementName);

            if (!elementObj) {
                return;
            }

            elementObj.hide();

            if (obj.value == 1) {
                elementObj.show();
            }
        },

        // ---------------------------------------

        initCustomInsertsPopup: function () {
            var popup = jQuery('#custom_inserts_popup');
            if (!popup.find('form').length) {
                popup.wrapInner('<form id="description_custom_inserts_form"></form>');
                CommonObj.initFormValidation('#description_custom_inserts_form');
            }

            popup.modal({
                title: TikTokShop.translator.translate('Custom Insertions'),
                type: 'slide',
                buttons: [],
                closed: function () {
                    TikTokShopTemplateDescriptionObj.customInsertsOnClosePopup();
                }
            });
        },

        customInsertsOpenPopup: function () {
            jQuery('#custom_inserts_popup').modal('openModal');
        },

        customInsertsOnClosePopup: function () {
            jQuery('#description_custom_inserts_form').trigger('reset').validate().resetForm();
        },

        // ---------------------------------------

        customInsertsClosePopup: function (callback) {
            jQuery('#custom_inserts_popup').modal({
                closed: function () {
                    callback && callback();

                    // prevent callback closure
                    callback = undefined;

                    TikTokShopTemplateDescriptionObj.customInsertsOnClosePopup();
                }
            }).modal('closeModal');
        },

        insertProductAttribute: function () {
            var self = this;

            self.customInsertsClosePopup(function () {
                self.appendToTextarea('#' + $('custom_inserts_product_attribute').value + '#');
            });
        },

        insertTikTokShopAttribute: function () {
            var self = this;

            self.customInsertsClosePopup(function () {
                self.appendToTextarea('#value[' + $('custom_inserts_tiktokshop_attribute').value + ']#');
            });
        },

        // ---------------------------------------

        initPreviewPopup: function () {
            var popup = jQuery('#description_preview_popup');
            if (!popup.find('form').length) {
                popup.wrapInner(new Element('form', {
                    id: 'description_preview_form',
                    method: 'post',
                    target: '_blank',
                    action: TikTokShop.url.get('tiktokshop_template_description/preview')
                }));
                this.initFormValidation('#description_preview_form');
            }

            popup.modal({
                title: TikTokShop.translator.translate('Description Preview'),
                type: 'popup',
                buttons: [{
                    text: TikTokShop.translator.translate('Cancel'),
                    class: 'action-secondary action-dismiss',
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: TikTokShop.translator.translate('Confirm'),
                    class: 'action-primary action-accept',
                    click: function (event) {
                        if (!jQuery('#description_preview_form').valid()) {
                            return;
                        }

                        $('description_preview_description_mode').value = $('description_mode').value;
                        $('description_preview_description_template').value = $('description_template').value;

                        $('description_preview_form').submit();

                        this.closeModal(event);
                    }
                }],
                closed: function () {
                    jQuery('#description_preview_form').trigger('reset').validate().resetForm();
                }
            });
        },

        openPreviewPopup: function () {
            if (
                    $('description_mode').value == TikTokShop.php.constant('\\M2E\\TikTokShop\\Model\\Template\\Description::DESCRIPTION_MODE_CUSTOM')
                    && !$('description_template').value.length
            ) {
                this.alert(TikTokShop.translator.translate('Please enter Description Value.'));
                return;
            }

            jQuery('#description_preview_popup').modal('openModal');
        },

        selectProductIdRandomly: function () {
            var self = this;

            new Ajax.Request(TikTokShop.url.get('tiktokshop_template_description/getRandomMagentoProductId'), {
                method: 'post',
                parameters: {
                    store_id: $('description_preview_store_id').value
                },
                onSuccess: function (transport) {
                    var response = transport.responseText.evalJSON();

                    if (response.success) {
                        $('description_preview_magento_product_id').value = response.product_id;
                    } else {
                        self.alert(response.message);
                    }
                }
            });
        },

        appendToTextarea: function (value) {
            if (value == '') {
                return;
            }

            if (typeof tinymce != 'undefined' && typeof tinymce.get('description_template') != 'undefined'
                    && tinymce.get('description_template') != null) {

                var data = tinymce.get('description_template').getContent();
                tinymce.get('description_template').setContent(data + value);

                return;
            }

            var element = $('description_template');

            if (document.selection) {

                /* IE */
                element.focus();
                document.selection.createRange().text = value;
                element.focus();

            } else if (element.selectionStart || element.selectionStart == '0') {

                /* Webkit */
                var startPos = element.selectionStart;
                var endPos = element.selectionEnd;
                var scrollTop = element.scrollTop;
                element.value = element.value.substring(0, startPos) + value + element.value.substring(endPos, element.value.length);
                element.focus();
                element.selectionStart = startPos + value.length;
                element.selectionEnd = startPos + value.length;
                element.scrollTop = scrollTop;

            } else {

                element.value += value;
                element.focus();
            }
        }
        // ---------------------------------------
    });
});
