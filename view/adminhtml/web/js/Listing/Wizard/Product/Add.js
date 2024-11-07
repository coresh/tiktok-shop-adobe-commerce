define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'TikTokShop/Common'
], function (jQuery, modal) {
    window.ListingWizardProductAdd = Class.create(Common, {

        // ---------------------------------------

        options: {
            show_autoaction_popup: false,

            get_selected_products: function (callback) {
            }
        },

        // ---------------------------------------

        initialize: function (options) {
            this.options = Object.extend(this.options, options);
        },

        // ---------------------------------------

        continue: function () {
            var self = this;

            self.options.get_selected_products(function (selectedProducts) {

                if (!selectedProducts) {
                    self.alert(TikTokShop.translator.translate('Please select the Products you want to perform the Action on.'));
                    return;
                }

                self.add(selectedProducts);
            });
        },

        // ---------------------------------------

        add: function (products) {
            const self = this;

            self.products = products;

            const parts = self.makeProductsParts();

            ProgressBarObj.reset();
            ProgressBarObj.setTitle('Adding Products to Listing');
            ProgressBarObj.setStatus('Adding in process. Please wait...');
            ProgressBarObj.show();
            self.scrollPageToTop();

            WrapperObj.lock();

            self.sendPartsProducts(parts, parts.length);
        },

        makeProductsParts: function () {
            const self = this;

            const productsInPart = 50;
            const productsArray = explode(',', self.products);
            let parts = [];

            if (productsArray.length < productsInPart) {

                parts[0] = productsArray;

                return parts;
            }

            let result = [];
            for (let i = 0; i < productsArray.length; i++) {
                if (result.length === 0 || result[result.length - 1].length === productsInPart) {
                    result[result.length] = [];
                }
                result[result.length - 1][result[result.length - 1].length] = productsArray[i];
            }

            return result;
        },

        sendPartsProducts: function (parts, partsCount) {
            const self = this;

            if (parts.length === 0) {
                return;
            }

            let part = parts.splice(0, 1);
            part = part[0];
            const partString = implode(',', part);

            new Ajax.Request(TikTokShop.url.get('listing_wizard_product_add_with_id'), {
                method: 'post',
                parameters: {
                    products: partString,
                },
                onSuccess: function (transport) {

                    var percents = (100 / partsCount) * (partsCount - parts.length);

                    if (percents <= 0) {
                        ProgressBarObj.setPercents(0, 0);
                    } else if (percents >= 100) {
                        ProgressBarObj.setPercents(100, 0);
                        ProgressBarObj.setStatus('Adding has been completed.');

                        setLocation(TikTokShop.url.get('listing_wizard_product_complete_with_id'));
                    } else {
                        ProgressBarObj.setPercents(percents, 1);
                    }

                    setTimeout(function () {
                        self.sendPartsProducts(parts, partsCount);
                    }, 500);
                }
            });

            $$('.loading-mask').invoke('setStyle', {visibility: 'hidden'});
        },
    });
});
