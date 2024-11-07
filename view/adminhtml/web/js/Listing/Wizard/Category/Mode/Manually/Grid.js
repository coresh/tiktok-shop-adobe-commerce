define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'TikTokShop/Plugin/Messages',
    'mage/translate',
    'TikTokShop/Grid'
], function (jQuery, modal, MessageObj, $t) {

    window.ListingWizardCategoryModeManuallyGrid = Class.create(Grid, {

        // ---------------------------------------

        productIdCellIndex: 1,
        productTitleCellIndex: 2,

        // ---------------------------------------

        prepareActions: function () {

            this.actions = {
                editCategoriesAction: function (id) {
                    id && this.selectByRowId(id);
                    this.editCategories();
                }.bind(this),

                resetCategoriesAction: function (id) {
                    this.resetCategories(id);
                }.bind(this),

                removeItemAction: function (id) {
                    var ids = id ? [id] : this.getSelectedProductsArray();
                    this.removeItems(ids);
                }.bind(this)
            };
        },

        // ---------------------------------------

        extractIdFromUrl: function () {
            const urlParts = window.location.href.split('/');
            const idIndex = urlParts.indexOf('id');
            if (idIndex !== -1 && idIndex < urlParts.length - 1) {
                return parseInt(urlParts[idIndex + 1]);
            }
        },

        // ---------------------------------------

        editCategories: function () {
            const self = this;

            this.selectedMagentoCategoryIds = this.getSelectedProductsString();
            const wizard_id = self.extractIdFromUrl();

            new Ajax.Request(TikTokShop.url.get('listing_wizard_category/chooserBlockModeManually'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids: this.selectedMagentoCategoryIds,
                    id: wizard_id
                },
                onSuccess: function (transport) {
                    this.openPopUp(
                            $t('Category Settings'),
                            transport.responseText
                    );
                }.bind(this)
            });
        },

        resetCategories: function (id) {
            if (id && !confirm('Are you sure?')) {
                return;
            }
            const self = this;
            const wizard_id = self.extractIdFromUrl();

            this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

            new Ajax.Request(TikTokShop.url.get('listing_wizard_category/resetModeManually'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids: this.selectedProductsIds.join(','),
                    id: wizard_id
                },
                onSuccess: function (transport) {
                    this.getGridObj().doFilter();
                    this.unselectAll();
                }.bind(this)
            });
        },

        validateCategories: function (isAlLeasOneCategorySelected, showErrorMessage) {
            MessageObj.setContainer('#anchor-content');
            MessageObj.clear();
            const button = $('listing_category_continue_btn');
            if (parseInt(isAlLeasOneCategorySelected)) {
                button.addClassName('disabled');
                button.disable();
                if (parseInt(showErrorMessage)) {
                    MessageObj.addWarning($t('To proceed, the category data must be specified.'));
                }
            } else {
                button.removeClassName('disabled');
                button.enable();
                MessageObj.clear();
            }
        },

        openPopUp: function (title, content) {
            const self = this;
            const popupId = 'modal_view_action_dialog';

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
                    text: $t('Cancel'),
                    attr: {id: 'cancel_button'},
                    class: 'action-dismiss',
                    click: function (event) {
                        self.unselectAllAndReload();
                        this.closeModal(event);
                        $(popupId).remove()
                    }
                }, {
                    text: $t('Save'),
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

            const selectedCategory = TikTokShopCategoryChooserObj.selectedCategory;

            this.saveCategoriesData(selectedCategory);
        },

        saveCategoriesData: function (templateData) {
            const self = this;
            const wizard_id = self.extractIdFromUrl();

            new Ajax.Request(TikTokShop.url.get('listing_wizard_category/saveModeManually'), {
                method: 'post',
                parameters: {
                    products_ids: this.getSelectedProductsString(),
                    template_data: Object.toJSON(templateData),
                    id: wizard_id
                },
                onSuccess: function (transport) {

                    jQuery('#modal_view_action_dialog').modal('closeModal');
                    this.unselectAllAndReload();
                }.bind(this)
            });
        },

        completeCategoriesDataStep: function (validateCategory, validateSpecifics) {
            MessageObj.clear();
            const self = this;
            const wizard_id = self.extractIdFromUrl();

            new Ajax.Request(TikTokShop.url.get('listing_wizard_category/validateModeManually'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    validate_category: validateCategory,
                    validate_specifics: validateSpecifics,
                    id: wizard_id
                },
                onSuccess: function (transport) {

                    const response = transport.responseText.evalJSON();

                    if (response.validation) {
                        return setLocation(
                            TikTokShop.url.get('listing_wizard_category/complete_step', {'id': wizard_id})
                        );
                    }

                    return MessageObj.addError(response.message || 'Something wrong');
                }.bind(this)
            });
        },
    });

});
