define([
    'underscore',
], function (_) {

    window.TikTokShopListingCreateGeneral = Class.create({

        accounts: null,
        selectedAccountId: null,

        // ---------------------------------------

        initialize: function (marketplaces) {
            var self = this;

            CommonObj.setValidationCheckRepetitionValue(
                    'TikTokShop-listing-title',
                    TikTokShop.translator.translate('The specified Title is already used for other Listing. Listing Title must be unique.'),
                    'Listing', 'title', 'id', null
            );

            self.initAccount();
            self.initMarketplace(marketplaces);
        },

        initAccount: function () {
            var self = this;

            jQuery('[data-add-account-btn]').on('click', function (e) {
                let target = e.currentTarget

                var win = window.open(target.getAttribute('data-url'));

                var intervalId = setInterval(function () {

                    if (!win.closed) {
                        return;
                    }

                    clearInterval(intervalId);

                    self.renderAccounts();

                }, 1000);
            });

            $('account_id').observe('change', function () {
                self.selectedAccountId = $('account_id').value || self.selectedAccountId;

                if (_.isNull(self.selectedAccountId)) {
                    return;
                }

                new Ajax.Request(TikTokShop.url.get('tiktokshop_shop/getShopsForAccount'), {
                    method: 'post',
                    parameters: {account_id: self.selectedAccountId},
                    onSuccess: function (transport) {
                        var response = JSON.parse(transport.responseText);
                        if (response.result) {
                            self.refreshShops(response.shops);
                            return;
                        }
                        throw response.message;
                    }
                })
            });

            self.renderAccounts();
        },

        refreshShops: function (shops) {
            var select = jQuery('#shop_id');
            select.find('option').remove();

            shops.each(function (shop) {
                select.append(new Option(shop.shop_name, shop.id))
            })
        },

        renderAccounts: function (callback) {
            let self = this;


            let accountAddBtn = $('add_account_button');
            let accountLabelEl = $('account_label');
            let accountSelectEl = $('account_id');
            let shopSelectField = $('shop_id').up('.field');

            new Ajax.Request(TikTokShop.url.get('general/getAccounts'), {
                method: 'get',
                onSuccess: function (transport) {
                    var accounts = transport.responseText.evalJSON();

                    if (_.isNull(self.accounts)) {
                        self.accounts = accounts;
                    }

                    if (_.isNull(self.selectedAccountId)) {
                        self.selectedAccountId = $('account_id').value;
                    }

                    var isAccountsChanged = !self.isAccountsEqual(accounts);

                    if (isAccountsChanged) {
                        self.accounts = accounts;
                    }

                    if (accounts.length === 0) {
                        accountAddBtn.down('span').update(TikTokShop.translator.translate('Add'));
                        accountLabelEl.update(TikTokShop.translator.translate('Account not found, please create it.'));
                        accountLabelEl.show();
                        accountSelectEl.hide();
                        shopSelectField.hide();
                        return;
                    }

                    accountSelectEl.update();
                    accountSelectEl.appendChild(new Element('option', {style: 'display: none'}));
                    accounts.each(function (account) {
                        accountSelectEl.appendChild(new Element('option', {value: account.id})).insert(account.title);
                    });

                    accountAddBtn.down('span').update(TikTokShop.translator.translate('Add Another'));

                    if (accounts.length === 1) {
                        var account = _.first(accounts);

                        $('account_id').value = account.id;
                        self.selectedAccountId = account.id;

                        var accountElement;

                        if (TikTokShop.formData.wizard) {
                            accountElement = new Element('span').update(account.title);
                        } else {
                            var accountLink = TikTokShop.url.get('tiktokshop_account/edit', {
                                'id': account.id,
                                close_on_save: 1
                            });
                            accountElement = new Element('a', {
                                'href': accountLink,
                                'target': '_blank'
                            }).update(account.title);
                        }

                        accountLabelEl.update(accountElement);

                        accountLabelEl.show();
                        accountSelectEl.dispatchEvent(new Event('change'));
                        accountSelectEl.hide();
                        shopSelectField.show();
                    } else if (isAccountsChanged) {
                        self.selectedAccountId = _.last(accounts).id;

                        accountLabelEl.hide();
                        accountSelectEl.show();
                        accountSelectEl.dispatchEvent(new Event('change'));
                        shopSelectField.show();
                    }

                    accountSelectEl.setValue(self.selectedAccountId);

                    callback && callback();
                }
            });
        },

        initMarketplace: function () {
            $$('.next_step_button').each(function (btn) {
                btn.observe('click', function () {
                    if (jQuery('#edit_form').valid()) {
                        CommonObj.saveClick(TikTokShop.url.get('tiktokshop_listing_create/index'), true);
                    }
                });
            });
        },

        isAccountsEqual: function (newAccounts) {
            if (!newAccounts.length && !this.accounts.length) {
                return true;
            }

            if (newAccounts.length !== this.accounts.length) {
                return false;
            }

            return _.every(this.accounts, function (account) {
                return _.where(newAccounts, account).length > 0;
            });
        }

        // ---------------------------------------
    });
});
