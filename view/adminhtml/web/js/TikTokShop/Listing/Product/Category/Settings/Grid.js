define([
    'TikTokShop/Plugin/Messages',
    'Magento_Ui/js/modal/modal',
    'TikTokShop/Grid'
], function (MessageObj, modal) {

    window.TikTokShopListingProductCategorySettingsGrid = Class.create(Grid, {

        // ---------------------------------------

        prepareActions: function () {
            this.actions = {
                editCategoriesAction: function (id) {
                    id && this.selectByRowId(id);
                    this.editCategories();
                }.bind(this),

                resetCategoriesAction: function (id) {
                    this.resetCategories(id);
                }.bind(this)
            };
        },

        editCategories: function () {
            this.selectedMagentoCategoryIds = this.getSelectedProductsString();

            new Ajax.Request(TikTokShop.url.get('tiktokshop_listing_product_category_settings/getChooserBlockHtml'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids: this.selectedMagentoCategoryIds,
                },
                onSuccess: function (transport) {
                    this.openPopUp(
                        TikTokShop.translator.translate('Category Settings'),
                        transport.responseText
                    );
                }.bind(this)
            });
        },

        resetCategories: function (id) {
            if (id && !confirm('Are you sure?')) {
                return;
            }

            this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

            new Ajax.Request(TikTokShop.url.get('tiktokshop_listing_product_category_settings/stepTwoReset'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids: this.selectedProductsIds.join(',')
                },
                onSuccess: function (transport) {
                    this.getGridObj().doFilter();
                    this.unselectAll();
                }.bind(this)
            });
        },

        //----------------------------------------

        openPopUp: function (title, content) {
            const self = this;
            let popupId = 'modal_view_action_dialog';

            let modalDialogMessage = $(popupId);

            if (!modalDialogMessage) {
                modalDialogMessage = new Element('form', {
                    id: popupId
                });
            }

            modalDialogMessage.innerHTML = '';

            this.popUp = jQuery(modalDialogMessage).modal(Object.extend({
                title: title,
                type: 'slide',
                buttons: [{
                    text: TikTokShop.translator.translate('Cancel'),
                    attr: {id: 'cancel_button'},
                    class: 'action-dismiss',
                    click: function (event) {
                        self.unselectAllAndReload();
                        this.closeModal(event);
                        $(popupId).remove()
                    }
                }, {
                    text: TikTokShop.translator.translate('Save'),
                    attr: {id: 'done_button'},
                    class: 'action-primary action-accept',
                    click: function (event) {
                        self.confirmCategoriesData();
                    }
                }]
            }));

            this.popUp.modal('openModal');

            try {
                modalDialogMessage.innerHTML = content;
                modalDialogMessage.innerHTML.evalScripts();
            } catch (ignored) {
            }
        },

        confirmCategoriesData: function () {
            this.initFormValidation('#modal_view_action_dialog');

            if (!jQuery('#modal_view_action_dialog').valid()) {
                return;
            }

            let selectedCategory = TikTokShopCategoryChooserObj.selectedCategory;

            this.saveCategoriesData(selectedCategory);
        },

        //----------------------------------------

        saveCategoriesData: function (templateData) {
            new Ajax.Request(TikTokShop.url.get('tiktokshop_listing_product_category_settings/stepTwoSaveToSession'), {
                method: 'post',
                parameters: {
                    products_ids: this.getSelectedProductsString(),
                    template_data: Object.toJSON(templateData)
                },
                onSuccess: function (transport) {

                    jQuery('#modal_view_action_dialog').modal('closeModal');
                    this.unselectAllAndReload();
                }.bind(this)
            });
        },

        // ---------------------------------------

        completeCategoriesDataStep: function (validateCategory, validateSpecifics) {
            MessageObj.clear();

            new Ajax.Request(TikTokShop.url.get('tiktokshop_listing_product_category_settings/stepTwoModeValidate'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    validate_category: validateCategory,
                    validate_specifics: validateSpecifics
                },
                onSuccess: function (transport) {

                    var response = transport.responseText.evalJSON();

                    if (response['validation']) {
                        return setLocation(TikTokShop.url.get('tiktokshop_listing_product_category_settings/step_assign_category'));
                    }

                    if (response['message']) {
                        return MessageObj.addError(response['message']);
                    }

                }.bind(this)
            });
        },

        // ---------------------------------------

        validateCategories: function (isAlLeasOneCategorySelected, showErrorMessage) {
            MessageObj.setContainer('#anchor-content');
            MessageObj.clear();
            var button = $('listing_category_continue_btn');
            if (parseInt(isAlLeasOneCategorySelected)) {
                button.addClassName('disabled');
                button.disable();
                if (parseInt(showErrorMessage)) {
                    MessageObj.addWarning(TikTokShop.translator.translate('select_relevant_category'));
                }
            } else {
                button.removeClassName('disabled');
                button.enable();
                MessageObj.clear();
            }
        },
    });

});
