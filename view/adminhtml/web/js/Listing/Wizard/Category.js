define([
    'TikTokShop/Plugin/Messages',
    'mage/translate',
    'TikTokShop/Common',
], function (MessagesObj, $t) {

    window.TikTokShopListingCategory = Class.create(Common, {

        // ---------------------------------------

        gridObj: null,
        selectedProductsIds: [],

        // ---------------------------------------

        initialize: function (gridObj) {
            this.gridObj = gridObj;
        },

        // ---------------------------------------

        getChooserSelectedCategory: function () {
            return TikTokShopCategoryChooserObj.selectedCategory;
        },

        getChooserSelectedAttributes: function () {
            return TikTokShopCategoryChooserObj.selectedSpecifics;
        },

        editCategorySettings: function (id) {
            this.selectedProductsIds = id ? [id] : this.gridObj.getSelectedProductsArray();

            const url = TikTokShop.url.get('listing_product_category_settings/edit');
            new Ajax.Request(url, {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids: this.selectedProductsIds.join(','),
                    shop_id: this.gridObj.shopId
                },
                onSuccess: function (transport) {
                    this.openPopUp($t('Category Settings'), transport.responseText);
                }.bind(this)
            });
        },

        saveCategorySettings: function () {
            this.initFormValidation('#modal_view_action_dialog');

            if (!jQuery('#modal_view_action_dialog').valid()) {
                return;
            }

            const self = this;
            const selectedCategory = this.getChooserSelectedCategory();
            selectedCategory.specific = this.getChooserSelectedAttributes();

            new Ajax.Request(TikTokShop.url.get('tiktokshop_listing/saveCategoryTemplate'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids: self.selectedProductsIds.join(','),
                    shop_id: self.gridObj.shopId,
                    template_category_id: selectedCategory.dictionaryId
                },
                onSuccess: function (transport) {
                    self.cancelCategorySettings();
                }.bind(this)
            });
        },

        // ---------------------------------------

        cancelCategorySettings: function () {
            jQuery('#modal_view_action_dialog').modal('closeModal');
        },

        // ---------------------------------------

        openPopUp: function (title, content, params, popupId) {
            const self = this;
            params = params || {};
            popupId = popupId || 'modal_view_action_dialog';

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
                        this.closeModal(event);
                    }
                }, {
                    text: $t('Save'),
                    attr: {id: 'done_button'},
                    class: 'action-primary action-accept',
                    click: function () {
                        self.saveCategorySettings();
                    }
                }],
                closed: function () {
                    self.selectedProductsIds = [];
                    self.selectedCategoriesData = {};

                    self.gridObj.unselectAllAndReload();

                    return true;
                }
            }, params));

            this.popUp.modal('openModal');

            try {
                modalDialogMessage.innerHTML = content;
                modalDialogMessage.innerHTML.evalScripts();
            } catch (ignored) {
            }
        },

        //----------------------------------------

        modeSameSubmitData: function (url) {
            let selectedCategory = this.getChooserSelectedCategory();

            if (Object.keys(selectedCategory).length === 0) {
                MessagesObj.clearAll();
                MessagesObj.addError($t('Please choose a category to continue.'));

                return;
            }

            if (!selectedCategory.is_all_required_attributes_filled) {
                MessagesObj.clearAll();
                MessagesObj.addError($t('Please complete all required attributes to proceed.'));

                return;
            }

            if (typeof selectedCategory !== 'undefined') {
                selectedCategory['specific'] = this.getChooserSelectedAttributes();
            }

            this.postForm(url, {category_data: Object.toJSON(selectedCategory)});
        }
    });
});
