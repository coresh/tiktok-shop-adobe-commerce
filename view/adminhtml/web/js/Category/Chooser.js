define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'TikTokShop/Plugin/Messages',
    'mage/translate',
    'TikTokShop/Common',
    'TikTokShop/Plugin/Magento/AttributeCreator'
], function (jQuery, modal, MessageObj, $t) {
    window.TikTokShopCategoryChooser = Class.create(Common, {

        // ---------------------------------------

        shopId: null,
        accountId: null,

        categoryInfoBlockMessages: null,
        categoryChangeBlockMessages: null,

        selectedCategory: {},
        selectedSpecifics: {},

        // ---------------------------------------

        initialize: function (shopId, accountId) {
            this.shopId = shopId;
            this.accountId = accountId;

            this.categoryInfoBlockMessages = Object.create(MessageObj);
            this.categoryInfoBlockMessages.setContainer('#category_info_messaged_container');

            this.categoryChangeBlockMessages = Object.create(MessageObj);
            this.categoryChangeBlockMessages.setContainer('#change_category_messaged_container');
        },

        initObservers: function () {
            const self = this;

            jQuery('#edit_category').on('click', function() {
                self.showEditCategoryPopUp();
            });

            jQuery('#edit_attributes').on('click', function() {
                self.editAttributes();
            });
        },

        // ---------------------------------------

        getShopId: function () {
            return this.shopId;
        },

        getAccountId: function () {
            return this.accountId;
        },

        setSelectedCategory: function (category) {
            this.selectedCategory.value = category;
        },

        getSelectedCategory: function () {
            if (this.isCategorySelected()) {
                return this.selectedCategory;
            }

            return {
                value: '',
                path: '',
            };
        },

        // ---------------------------------------

        showEditCategoryPopUp: function () {
            this.messagesClearAll();
            const self = this;
            const selected = self.getSelectedCategory();

            new Ajax.Request(TikTokShop.url.get('tiktokshop_category/getChooserEditHtml'), {
                method: 'post',
                parameters: {
                    shop_id: self.shopId,
                    account_id: self.accountId,
                    selected_value: selected.value,
                    selected_path: selected.path,
                    view_mode: 'with_tabs',
                },
                onSuccess: function (transport) {
                    self.openPopUp($t('Change Category'), transport.responseText);
                    self.renderRecent();

                    let categoryPathElement = $('selected_category_container').down('#selected_category_path');
                    categoryPathElement.innerHTML = self.cutDownLongPath(categoryPathElement.innerHTML.trim(), 130, '&gt;');
                }
            });
        },

        openPopUp: function (title, html) {
            const self = this;
            let chooserContainer = $('chooser_container');

            if (chooserContainer) {
                chooserContainer.remove();
            }

            $('html-body').insert({bottom: html});

            jQuery('#category_search').applyBindings();

            let content = jQuery('#chooser_container');

            modal({
                title: title,
                type: 'slide',
                buttons: [{
                    class: 'template_category_chooser_cancel',
                    text: $t('Cancel'),
                    click: function () {
                        self.cancelPopUp();
                    }
                }, {
                    class: 'action primary template_category_chooser_confirm',
                    text: $t('Confirm'),
                    click: function () {
                        self.confirmCategory();
                    }
                }]
            }, content);

            content.modal('openModal');
        },

        // ---------------------------------------

        cancelPopUp: function () {
            jQuery('#chooser_container').modal('closeModal');
        },

        // ---------------------------------------

        selectCategory: function (categoryId) {
            this.messagesClearAll();
            const self = this;

            new Ajax.Request(TikTokShop.url.get('tiktokshop_category/getSelectedCategoryDetails'), {
                method: 'post',
                parameters: {
                    shop_id: self.shopId,
                    value: categoryId,
                },
                onSuccess: function (transport) {
                    const response = transport.responseText.evalJSON();

                    self.selectedCategory = {
                        value: categoryId,
                        path: response.path,
                    };

                    let pathElement = $('selected_category_path');
                    if (pathElement) {
                        let interfacePath = response.path + '(' + categoryId + ')';
                        pathElement.setAttribute('title', interfacePath);
                        pathElement.innerHTML = self.cutDownLongPath(interfacePath, 130, '>');
                    }

                    let resetLink = $('category_reset_link');
                    if (resetLink) {
                        resetLink.show();
                    }
                }
            });
        },

        unSelectCategory: function () {

            this.selectedCategory = {};

            let selectedCategoryPath = $('selected_category_path'),
                    resetLink = $('category_reset_link');

            if (resetLink) {
                resetLink.hide();
            }

            if (selectedCategoryPath) {
                selectedCategoryPath.innerHTML = '<span style="color: grey; font-style: italic">'
                        + $t('Not Selected')
                        + '</span>';
            }
        },

        confirmCategory: function () {
            const self = this;

            self.messagesClearAll();

            if (self.isCategorySelected() && self.selectedCategory.value) {
                new Ajax.Request(TikTokShop.url.get('tiktokshop_category/getEditedCategoryInfo'), {
                    method: 'post',
                    parameters: {
                        shop_id: self.shopId,
                        category_id: self.selectedCategory.value,
                    },
                    onSuccess: function (transport) {
                        let response = transport.responseText.evalJSON();

                        if (response.hasOwnProperty('success') && !response.success) {
                            self.messageAddErrorToCategoryInfoBlock(response.message);

                            return;
                        }

                        self.selectedCategory.dictionaryId = response.dictionary_id;
                        self.selectedCategory.is_all_required_attributes_filled = response.is_all_required_attributes_filled;
                        self.selectedCategory.path = response.path;

                        self.reload();
                    }
                });
            } else {
                self.messageAddErrorToCategoryChangeBlock($t('Please select a category to continue.'));

                return;
            }

            jQuery('#chooser_container').modal('closeModal');
        },

        reload: function () {
            if (!this.isCategorySelected()) {
                jQuery('#category_path').text('Not selected')
                this.unsetIsRequiredAttributes();
                jQuery('#attributes_wrapper').addClass('hidden');

                return;
            }

            if (this.selectedCategory.path) {
                jQuery('#category_path').text(this.selectedCategory.path)
                jQuery('#attributes_wrapper').removeClass('hidden');
            }

            if (this.selectedCategory.dictionaryId) {
                this.getCountsOfSpecifics(this.selectedCategory.dictionaryId, function (used, total) {
                    jQuery('#attributes_counts').text(used + '/' + total);
                });
            } else {
                jQuery('#attributes_counts').text('0/0');
            }

            if (this.selectedCategory.is_all_required_attributes_filled) {
                this.unsetIsRequiredAttributes();
                return;
            }

            if (this.selectedCategory.is_all_required_attributes_filled) {
                this.unsetIsRequiredAttributes();
            } else {
                this.setIsRequiredAttributes();
            }
        },

        setIsRequiredAttributes: function () {
            jQuery('#attributes_required').removeClass('hidden');
        },

        unsetIsRequiredAttributes: function () {
            jQuery('#attributes_required').addClass('hidden');
        },

        attributesIsRequired: function () {
            return !jQuery('#attributes_required').hasClass('hidden');
        },

        isCategorySelected: function () {
            return Object.keys(this.selectedCategory).length !== 0;
        },

        isSpecificsSelected: function () {
            return Object.keys(this.selectedSpecifics).length !== 0;
        },

        getCountsOfSpecifics: function (dictionaryId, callback) {
            new Ajax.Request(TikTokShop.url.get('tiktokshop_category/getCountsOfAttributes'), {
                method: 'post',
                parameters: {
                    dictionary_id: dictionaryId,
                },
                onSuccess: function (transport) {
                    const counts = transport.responseText.evalJSON();
                    callback(counts.used, counts.total);
                }
            });
        },

        // ---------------------------------------

        renderRecent: function () {
            const self = this;

            if (!$('chooser_recent_table')) {
                return;
            }

            new Ajax.Request(TikTokShop.url.get('tiktokshop_category/getRecent'), {
                method: 'post',
                parameters: {
                    shop_id: self.shopId,
                    selected_category: null
                },
                onSuccess: function (transport) {
                    const categories = transport.responseText.evalJSON();
                    let html = '';

                    if (transport.responseText.length > 2) {
                        html += '<tr><td width="730px"></td><td width="70px"></td></tr>';
                        categories.each(function (category) {
                            html += '<tr><td>' + category.path + '</td>' +
                                    '<td style="width: 60px"><a href="javascript:void(0)" ' +
                                    'onclick="TikTokShopCategoryChooserObj.selectCategory(\'' + category.id + '\')">' +
                                    $t('Select') + '</a></td></tr>';
                        });
                    } else {
                        html += '<tr><td colspan="2" style="padding-left: 200px"><strong>' + $t('No saved Categories') + '</strong></td></tr>';
                    }

                    $('chooser_recent_table').innerHTML = html;
                }
            });
        },

        // ---------------------------------------

        cutDownLongPath: function (path, length, sep) {
            if (path.length > length && sep) {

                var parts = path.split(sep),
                        isNeedSeparator = false;

                var shortPath = '';
                parts.each(function (part, index) {
                    if ((part.length + shortPath.length) >= length) {

                        var lenDiff = (parts[parts.length - 1].length + shortPath.length) - length;
                        if (lenDiff > 0) {
                            shortPath = shortPath.slice(0, shortPath.length - lenDiff + 1);
                        }

                        shortPath = shortPath.slice(0, shortPath.length - 3) + '...';

                        shortPath += parts[parts.length - 1];
                        throw $break;
                    }

                    shortPath += part + (isNeedSeparator ? sep : '');
                    isNeedSeparator = true;
                });

                return shortPath;
            }

            return path;
        },

        // ---------------------------------------

        editAttributes: function () {
            const self = this;

            self.messagesClearAll();

            let selectedCategory = this.getSelectedCategory();

            new Ajax.Request(TikTokShop.url.get('tiktokshop_category/getCategoryAttributesHtml'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    dictionary_id: selectedCategory.dictionaryId,
                },
                onSuccess: function (transport) {
                    self.openSpecificsPopUp($t('Specifics'), transport.responseText);
                }.bind(this)
            });
        },

        openSpecificsPopUp: function (title, html) {
            const self = this;
            if ($('chooser_container_specific')) {
                $('chooser_container_specific').remove();
            }

            $('html-body').insert({bottom: html});

            const content = jQuery('#chooser_container_specific');

            modal({
                title: title,
                type: 'slide',
                buttons: [{
                    class: 'template_category_specific_chooser_cancel',
                    text: $t('Cancel'),
                    click: function () {
                        this.closeModal();
                    }
                }, {
                    class: 'action primary template_category_specific_chooser_reset',
                    text: $t('Reset'),
                    click: function () {
                        TikTokShopTemplateCategorySpecificsObj.resetSpecifics();
                    }
                }, {
                    class: 'action primary template_category_specific_chooser_save',
                    text: $t('Save'),
                    click: function () {
                        self.confirmSpecifics();
                    }
                }]
            }, content);

            content.modal('openModal');
        },

        confirmSpecifics: function () {
            this.initFormValidation('#edit_specifics_form');
            if (!jQuery('#edit_specifics_form').valid()) {
                return;
            }

            const self = TikTokShopCategoryChooserObj;

            this.selectedSpecifics = TikTokShopTemplateCategorySpecificsObj.collectSpecifics();

            new Ajax.Request(TikTokShop.url.get('tiktokshop_category/saveCategoryAttributesAjax'), {
                method: 'post',
                parameters: {
                    dictionary_id: self.selectedCategory.dictionaryId,
                    attributes: JSON.stringify(this.selectedSpecifics),
                },
                onSuccess: function (transport) {

                    const response = transport.responseText.evalJSON();

                    self.messagesClearAll();
                    if (response.success) {
                        self.selectedCategory.is_all_required_attributes_filled = true;
                        self.messageAddSuccessToCategoryInfoBlock($t('Attributes was saved'));
                    } else {
                        self.messageAddErrorToCategoryInfoBlock($t('Attributes not saved'));
                    }

                    self.reload();
                    jQuery('#chooser_container_specific').modal('closeModal');
                }
            });
        },

        resetSpecificsToDefault: function () {
            const self = TikTokShopCategoryChooserObj,
                    selectedCategory = this.getSelectedCategory();

            new Ajax.Request(TikTokShop.url.get('tiktokshop_category/getSelectedCategoryDetails'), {
                method: 'post',
                parameters: {
                    shop_id: self.shopId,
                    value: selectedCategory.value,
                },
                onSuccess: function (transport) {

                    self.selectedSpecifics = {};

                    self.reload();
                }
            });
        },

        // ----------------------------------------

        messagesClearAll: function () {
            this.messagesClearOnCategoryInfoBlock();
            this.messagesClearOnCategoryChangeBlock();
        },

        // ----------------------------------------

        messagesClearOnCategoryInfoBlock: function () {
            this.categoryInfoBlockMessages.clearAll();
        },

        messageAddErrorToCategoryInfoBlock: function (message) {
            this.messagesClearOnCategoryInfoBlock();
            this.categoryInfoBlockMessages.addError(message);
        },

        messageAddSuccessToCategoryInfoBlock: function (message) {
            this.messagesClearOnCategoryInfoBlock();
            this.categoryInfoBlockMessages.addSuccess(message);
        },

        messagesClearOnCategoryChangeBlock: function (message) {
            this.categoryChangeBlockMessages.clearAll();
        },

        messageAddErrorToCategoryChangeBlock: function (message) {
            this.messagesClearOnCategoryChangeBlock();
            this.categoryChangeBlockMessages.addError(message);
        }
    });
});
