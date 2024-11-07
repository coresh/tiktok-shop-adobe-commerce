define([
    'jquery',
    'TikTokShop/Common'
], function (jQuery, modal, messageObj) {
    window.TikTokShopListingVariationProductManage = Class.create(Common, {
        openPopUp: function (productId, title, filterByIds) {
            const self = this;

            let requestParams = {
                product_id: productId
            };

            if (filterByIds) {
                requestParams['filter_by_ids'] = filterByIds
            }

            new Ajax.Request(TikTokShop.url.get('variationProductManageOpenPopupUrl'), {
                method: 'post',
                parameters: requestParams,
                onSuccess: function (transport) {

                    var modalContainer = self.getModalContainer('modal_variation_product_manage')

                    window.variationProductManagePopup = jQuery(modalContainer).modal({
                        title: title.escapeHTML(),
                        type: 'slide',
                        buttons: []
                    });
                    variationProductManagePopup.modal('openModal');

                    modalContainer.insert(transport.responseText);
                    modalContainer.innerHTML.evalScripts();

                    variationProductManagePopup.productId = productId;
                }
            });
        },

        getModalContainer: function (containerId)
        {
            let modalContainer = $(containerId);
            if (!modalContainer) {
                modalContainer = new Element('div', { id: containerId });
            } else {
                modalContainer.innerHTML = '';
            }

            return modalContainer
        }
    });
});
