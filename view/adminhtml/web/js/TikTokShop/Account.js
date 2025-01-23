define([
    'Magento_Ui/js/modal/modal',
    'mage/validation',
    'TikTokShop/Common',
    'extjs/ext-tree-checkbox',
    'mage/adminhtml/form'
], function (modal) {

    window.TikTokShopAccount = Class.create(Common, {

        afterRefreshData: undefined,

        // ---------------------------------------

        initialize: function (id, afterRefreshData) {
            this.afterRefreshData = afterRefreshData

            jQuery.validator.addMethod('TikTokShop-account-customer-id', function (value) {

                var checkResult = false;

                if ($('magento_orders_customer_id_container').getStyle('display') == 'none') {
                    return true;
                }

                new Ajax.Request(TikTokShop.url.get('general/checkCustomerId'), {
                    method: 'post',
                    asynchronous: false,
                    parameters: {
                        customer_id: value,
                        id: TikTokShop.formData.id
                    },
                    onSuccess: function (transport) {
                        checkResult = transport.responseText.evalJSON()['ok'];
                    }
                });

                return checkResult;
            }, TikTokShop.translator.translate('No Customer entry is found for specified ID.'));


            jQuery.validator.addMethod(
                    'TikTokShop-require-select-attribute',
                    function (value, el) {
                        if ($('other_listings_mapping_mode').value == 0) {
                            return true;
                        }

                        var isAttributeSelected = false;

                        $$('.attribute-mode-select').each(function (obj) {
                            if (obj.value != 0) {
                                isAttributeSelected = true;
                            }
                        });

                        return isAttributeSelected;
                    },
                    TikTokShop.translator.translate(
                            'If Yes is chosen, you must select at least one Attribute for Product Linking.'
                    )
            );
        },

        initObservers: function () {

            if ($('tikTokShopAccountEditTabs_listingOther')) {

                $('other_listings_synchronization')
                        .observe('change', this.other_listings_synchronization_change)
                        .simulate('change');
                $('other_listings_mapping_mode')
                        .observe('change', this.other_listings_mapping_mode_change)
                        .simulate('change');
                $('mapping_sku_mode')
                        .observe('change', this.mapping_sku_mode_change)
                        .simulate('change');
                $('mapping_title_mode')
                        .observe('change', this.mapping_title_mode_change)
                        .simulate('change');
                $('mapping_item_id_mode')
                        .observe('change', this.mapping_item_id_mode_change)
                        .simulate('change');
            }

            if ($('tikTokShopAccountEditTabs_order')) {

                $('magento_orders_listings_mode')
                        .observe('change', this.magentoOrdersListingsModeChange)
                        .simulate('change');
                $('magento_orders_listings_store_mode')
                        .observe('change', this.magentoOrdersListingsStoreModeChange)
                        .simulate('change');

                $('magento_orders_listings_other_mode')
                        .observe('change', this.magentoOrdersListingsOtherModeChange)
                        .simulate('change');

                $('magento_orders_number_source')
                        .observe('change', this.magentoOrdersNumberChange);
                $('magento_orders_number_prefix_prefix')
                        .observe('keyup', this.magentoOrdersNumberChange);

                TikTokShopAccountObj.renderOrderNumberExample();

                $('magento_orders_customer_mode')
                        .observe('change', this.magentoOrdersCustomerModeChange)
                        .simulate('change');

                $('magento_orders_status_mapping_mode').observe('change', TikTokShopAccountObj.magentoOrdersStatusMappingModeChange);

                $('order_number_example-note').previous().remove();
            }

            if (this.afterRefreshData) {
                this.isValidForm();
            }
        },

        // ---------------------------------------

        saveAndClose: function () {
            var self = this,
                    url = typeof TikTokShop.url.urls.formSubmit == 'undefined' ?
                            TikTokShop.url.formSubmit + 'back/' + Base64.encode('list') + '/' :
                            TikTokShop.url.get('formSubmit', {'back': Base64.encode('list')});

            if (!this.isValidForm()) {
                return;
            }

            new Ajax.Request(url, {
                method: 'post',
                parameters: Form.serialize($('edit_form')),
                onSuccess: function (transport) {
                    transport = transport.responseText.evalJSON();

                    if (transport.success) {
                        window.close();
                    } else {
                        self.alert(transport.message);
                    }
                }
            });
        },

        // ---------------------------------------

        deleteClick: function (id) {
            this.confirm({
                content: TikTokShop.translator.translate('confirmation_account_delete'),
                actions: {
                    confirm: function () {
                        if (id === undefined) {
                            setLocation(TikTokShop.url.get('deleteAction'));
                        } else {
                            setLocation(TikTokShop.url.get('*/tiktokshop_account/delete', {
                                id: id,
                            }));
                        }
                    },
                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        // ---------------------------------------

        get_token: function () {
            this.submitForm(TikTokShop.url.get('tiktokshop_account/beforeGetToken', {'id': TikTokShop.formData.id}));
        },

        // ---------------------------------------

        magentoOrdersListingsModeChange: function () {
            var self = TikTokShopAccountObj;

            if ($('magento_orders_listings_mode').value == 1) {
                $('magento_orders_listings_store_mode_container').show();
            } else {
                $('magento_orders_listings_store_mode_container').hide();
                $('magento_orders_listings_store_mode').value = TikTokShop.php.constant('Account\\Settings\\Order::LISTINGS_STORE_MODE_DEFAULT');
            }

            self.magentoOrdersListingsStoreModeChange();
            self.changeVisibilityForOrdersModesRelatedBlocks();
        },

        magentoOrdersListingsStoreModeChange: function () {
            if ($('magento_orders_listings_store_mode').value == TikTokShop.php.constant('Account\\Settings\\Order::LISTINGS_STORE_MODE_CUSTOM')) {
                $('magento_orders_listings_store_id_container').show();
            } else {
                $('magento_orders_listings_store_id_container').hide();
                $('magento_orders_listings_store_id').value = '';
            }
        },

        magentoOrdersStatusMappingModeChange: function() {
            // Reset dropdown selected values to default
            $('magento_orders_status_mapping_processing').value = TikTokShop.php.constant('Account\\Settings\\Order::ORDERS_STATUS_MAPPING_PROCESSING');
            $('magento_orders_status_mapping_shipped').value = TikTokShop.php.constant('Account\\Settings\\Order::ORDERS_STATUS_MAPPING_SHIPPED');

            var disabled = $('magento_orders_status_mapping_mode').value == TikTokShop.php.constant('Account\\Settings\\Order::ORDERS_STATUS_MAPPING_MODE_DEFAULT');
            $('magento_orders_status_mapping_processing').disabled = disabled;
            $('magento_orders_status_mapping_shipped').disabled = disabled;
        },

        magentoOrdersListingsOtherModeChange: function () {
            var self = TikTokShopAccountObj;

            if ($('magento_orders_listings_other_mode').value == 1) {
                $('magento_orders_listings_other_store_id_container').show();
            } else {
                $('magento_orders_listings_other_store_id_container').hide();
                $('magento_orders_listings_other_store_id').value = '';
            }

            self.magentoOrdersListingsOtherProductModeChange();
            self.changeVisibilityForOrdersModesRelatedBlocks();
        },

        magentoOrdersListingsOtherProductModeChange: function () {
            $('magento_orders_listings_other_product_tax_class_id_container').hide();
            $('magento_orders_listings_other_product_mode_warning').hide();
        },

        magentoOrdersNumberChange: function () {
            var self = TikTokShopAccountObj;
            self.renderOrderNumberExample();
        },

        renderOrderNumberExample: function () {
            var orderNumber = '123456789';
            if ($('magento_orders_number_source').value == TikTokShop.php.constant('Account\\Settings\\Order::NUMBER_SOURCE_CHANNEL')) {
                orderNumber = '123412341234123100';
            }

            orderNumber = $('magento_orders_number_prefix_prefix').value + orderNumber;

            $('order_number_example_container').update(orderNumber);
        },

        magentoOrdersCustomerModeChange: function () {
            var customerMode = $('magento_orders_customer_mode').value;

            if (customerMode == TikTokShop.php.constant('Account\\Settings\\Order::CUSTOMER_MODE_PREDEFINED')) {
                $('magento_orders_customer_id_container').show();
                $('magento_orders_customer_id').addClassName('TikTokShop-account-product-id');
            } else {  // TikTokShop.php.constant('Account\Settings\Order::ORDERS_CUSTOMER_MODE_GUEST') || TikTokShop.php.constant('Account\Settings\Order::CUSTOMER_MODE_NEW')
                $('magento_orders_customer_id_container').hide();
                $('magento_orders_customer_id').value = '';
                $('magento_orders_customer_id').removeClassName('TikTokShop-account-product-id');
            }

            var action = (customerMode == TikTokShop.php.constant('Account\\Settings\\Order::CUSTOMER_MODE_NEW')) ? 'show' : 'hide';
            $('magento_orders_customer_new_website_id_container')[action]();
            $('magento_orders_customer_new_group_id_container')[action]();
            $('magento_orders_customer_new_notifications_container')[action]();

            if (action == 'hide') {
                $('magento_orders_customer_new_website_id').value = '';
                $('magento_orders_customer_new_group_id').value = '';
                $('magento_orders_customer_new_notifications').value = '';
            }
        },

        changeVisibilityForOrdersModesRelatedBlocks: function () {
            var self = TikTokShopAccountObj;

            if ($('magento_orders_listings_mode').value == 0 && $('magento_orders_listings_other_mode').value == 0) {

                $('magento_block_tiktokshop_accounts_magento_orders_number-wrapper').hide();
                $('magento_orders_number_source').value = TikTokShop.php.constant('Account\\Settings\\Order::NUMBER_SOURCE_MAGENTO');

                $('magento_block_tiktokshop_accounts_magento_orders_customer-wrapper').hide();
                $('magento_orders_customer_mode').value = TikTokShop.php.constant('Account\\Settings\\Order::CUSTOMER_MODE_GUEST');
                self.magentoOrdersCustomerModeChange();

                $('magento_block_tiktokshop_accounts_magento_orders_rules-wrapper').hide();
                $('magento_orders_qty_reservation_days').value = 1;

                $('magento_block_tiktokshop_accounts_magento_orders_tax-wrapper').hide();
                $('magento_orders_tax_mode').value = TikTokShop.php.constant('Account\\Settings\\Order::TAX_MODE_MIXED');

                $('magento_orders_customer_billing_address_mode').value = TikTokShop.php.constant('Account\\Settings\\Order::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT');
            } else {
                $('magento_block_tiktokshop_accounts_magento_orders_number-wrapper').show();
                $('magento_block_tiktokshop_accounts_magento_orders_customer-wrapper').show();
                $('magento_block_tiktokshop_accounts_magento_orders_rules-wrapper').show();
                $('magento_block_tiktokshop_accounts_magento_orders_tax-wrapper').show();
            }
        },

        // ---------------------------------------

        other_listings_synchronization_change: function () {
            var relatedStoreViews = $('magento_block_tiktokshop_accounts_other_listings_related_store_views-wrapper');

            if (this.value == 1) {
                $('other_listings_mapping_mode_tr').show();
                $('other_listings_mapping_mode').simulate('change');
                if (relatedStoreViews) {
                    relatedStoreViews.show();
                }
            } else {
                $('other_listings_mapping_mode').value = 0;
                $('other_listings_mapping_mode').simulate('change');
                $('other_listings_mapping_mode_tr').hide();
                if (relatedStoreViews) {
                    relatedStoreViews.hide();
                }
            }
        },

        other_listings_mapping_mode_change: function () {
            if (this.value == 1) {
                $('magento_block_tiktokshop_accounts_other_listings_product_mapping-wrapper').show();
            } else {
                $('magento_block_tiktokshop_accounts_other_listings_product_mapping-wrapper').hide();

                $('mapping_sku_mode').value = TikTokShop.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_SKU_MODE_NONE');
                $('mapping_title_mode').value = TikTokShop.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_TITLE_MODE_NONE');
            }

            $('mapping_sku_mode').simulate('change');
            $('mapping_title_mode').simulate('change');
        },

        synchronization_mapped_change: function () {
            if (this.value == 0) {
                $('settings_button').hide();
            } else {
                $('settings_button').show();
            }
        },

        mapping_sku_mode_change: function () {
            var self = TikTokShopAccountObj,
                    attributeEl = $('mapping_sku_attribute');

            $('mapping_sku_priority').hide();
            if (this.value != TikTokShop.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_SKU_MODE_NONE')) {
                $('mapping_sku_priority').show();
            }

            attributeEl.value = '';
            if (this.value == TikTokShop.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, attributeEl);
            }
        },

        mapping_title_mode_change: function () {
            var self = TikTokShopAccountObj,
                    attributeEl = $('mapping_title_attribute');

            $('mapping_title_priority').hide();
            if (this.value != TikTokShop.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_TITLE_MODE_NONE')) {
                $('mapping_title_priority').show();
            }

            attributeEl.value = '';
            if (this.value == TikTokShop.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, attributeEl);
            }
        },

        mapping_item_id_mode_change: function () {
            var self = TikTokShopAccountObj,
                    attributeEl = $('mapping_item_id_attribute');

            $('mapping_item_id_priority').hide();
            if (this.value != TikTokShop.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_ITEM_ID_MODE_NONE')) {
                $('mapping_item_id_priority').show();
            }

            attributeEl.value = '';
            if (this.value == TikTokShop.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_ITEM_ID_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, attributeEl);
            }
        },

        // ---------------------------------------
    });

});
