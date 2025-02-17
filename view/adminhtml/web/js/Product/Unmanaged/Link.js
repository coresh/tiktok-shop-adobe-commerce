define([
    'jquery',
    'mage/translate'
], ($, $t) => {
    'use strict';

    return {
        openSelectProductPopUp: function (otherProductId, productTitle, url) {
            let title = $t('Linking Product');

            if (productTitle) {
                title = title + ' "' + productTitle + '"';
            }

            new Ajax.Request(url, {
                method: 'post',
                parameters: {
                    other_product_id: otherProductId
                },
                onSuccess: function (transport) {
                    let modalDialogMessage = $('map_modal_dialog_message');

                    if (modalDialogMessage) {
                        modalDialogMessage.remove();
                    }

                    modalDialogMessage = new Element('div', {
                        id: 'map_modal_dialog_message'
                    });

                    this.popUp = $(modalDialogMessage).modal({
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
    };
});
