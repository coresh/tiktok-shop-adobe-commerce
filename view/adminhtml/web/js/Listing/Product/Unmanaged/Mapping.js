define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'TikTokShop/Plugin/Messages',
    'mage/translate',
    'TikTokShop/Common'
], function (jQuery, modal, MessagesObj, $t) {
    window.ListingProductUnmanagedMapping = Class.create(Common, {

        // ---------------------------------------

        initialize: function (gridHandler) {
            this.gridHandler = gridHandler;
        },

        openPopUp: function (otherProductId, productTitle) {
            this.gridHandler.unselectAll();
            let self = this;
            let title = $t('Linking Product');

            if (productTitle) {
                title = title + ' "' + productTitle + '"';
            }

            new Ajax.Request(TikTokShop.url.get('mapProductPopupHtml'), {
                method: 'post',
                parameters: {
                    product_id: otherProductId
                },
                onSuccess: function (transport) {

                    var modalDialogMessage = $('map_modal_dialog_message');

                    if (modalDialogMessage) {
                        modalDialogMessage.remove();
                    }

                    modalDialogMessage = new Element('div', {
                        id: 'map_modal_dialog_message'
                    });

                    this.popUp = jQuery(modalDialogMessage).modal({
                        title: title,
                        type: 'slide',
                        buttons: []
                    });
                    this.popUp.modal('openModal');

                    modalDialogMessage.insert(transport.responseText);
                    $('other_product_id').value = otherProductId;
                }.bind(this)
            });
        },

        // ---------------------------------------

        map: function (productId) {
            let self = this;
            let otherProductId = $('other_product_id').value;

            MessagesObj.clearAll();

            if (otherProductId == '' || (/^\s*(\d)*\s*$/i).test(otherProductId) == false) {
                return;
            }

            if (productId == '' || (/^\s*(\d)*\s*$/i).test(productId) == false) {
                return;
            }

            if (!confirm($t('Are you sure?'))) {
                return;
            }

            new Ajax.Request(TikTokShop.url.get('listing_other_mapping/map', {}), {
                method: 'post',
                parameters: {
                    product_id: productId,
                    other_product_id: otherProductId
                },
                onSuccess: function (transport) {

                    let response = transport.responseText.evalJSON();
                    if (response.result) {
                        this.gridHandler.unselectAllAndReload();
                        this.popUp.modal('closeModal');
                        this.scrollPageToTop();
                        MessagesObj.addSuccess($t('Product(s) was Linked.'));
                    } else {
                        alert($t('Product Variation mismatch: please ensure both products share identical variation structures (e.g., size, color) before attempting to link them.'));
                    }
                }.bind(this)
            });
        },

        remap: function (productId) {
            let self = this;
            let listingProductId = $('other_product_id').value;

            MessagesObj.clearAll();

            if (listingProductId == '' || (/^\s*(\d)*\s*$/i).test(listingProductId) == false) {
                return;
            }

            if (productId == '' || (/^\s*(\d)*\s*$/i).test(productId) == false) {
                return;
            }

            if (!confirm($t('Are you sure?'))) {
                return;
            }

            new Ajax.Request(TikTokShop.url.get('listing_mapping/remap'), {
                method: 'post',
                parameters: {
                    product_id: productId,
                    listing_product_id: listingProductId
                },
                onSuccess: function (transport) {

                    let response = transport.responseText.evalJSON();

                    this.gridHandler.unselectAllAndReload();
                    this.popUp.modal('closeModal');
                    this.scrollPageToTop();

                    if (response.result) {
                        MessagesObj.addSuccess(response.message);
                    } else {
                        MessagesObj.addError(response.message);
                    }
                }.bind(this)
            });
        }
    });

    // ---------------------------------------
});
