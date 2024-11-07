define([
    'TikTokShop/Grid'
], function () {

    window.ListingWizardProductSummaryGrid = Class.create(Grid, {

        // ---------------------------------------

        prepareActions: function () {
            this.actions = {
                removeAction: this.remove.bind(this)
            };
        },

        // ---------------------------------------

        extractIdFromUrl: function () {
            var urlParts = window.location.href.split('/');
            var idIndex = urlParts.indexOf('id');
            if (idIndex !== -1 && idIndex < urlParts.length - 1) {
                return parseInt(urlParts[idIndex + 1]);
            }
        },

        // ---------------------------------------

        remove: function () {
            var self = this;
            let wizard_id = self.extractIdFromUrl();

            Grid.prototype.confirm({
                actions: {
                    confirm: function () {
                        var url = TikTokShop.url.get('listing_wizard_product/removeProductsByCategory');
                        new Ajax.Request(url, {
                            parameters: {
                                ids: self.getSelectedProductsString(),
                                id: wizard_id
                            },
                            onSuccess: self.unselectAllAndReload.bind(self)
                        });
                    },
                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        // ---------------------------------------

        confirm: function (config) {
            if (config.actions && config.actions.confirm) {
                config.actions.confirm();
            }
        }

        // ---------------------------------------
    });
});
