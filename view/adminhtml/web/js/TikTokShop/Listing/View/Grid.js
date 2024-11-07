define([
    'TikTokShop/Listing/View/Grid',
    'TikTokShop/TikTokShop/Listing/View/Action'
], function () {

    window.TikTokShopListingViewGrid = Class.create(ListingViewGrid, {

        // ---------------------------------------

        selectedProductsIds: [],

        // ---------------------------------------

        prepareActions: function ($super) {
            this.actionHandler = new TikTokShopListingViewAction(this);

            this.actions = {
                listAction: this.actionHandler.listAction.bind(this.actionHandler),
                relistAction: this.actionHandler.relistAction.bind(this.actionHandler),
                reviseAction: this.actionHandler.reviseAction.bind(this.actionHandler),
                stopAction: this.actionHandler.stopAction.bind(this.actionHandler),
                stopAndRemoveAction: this.actionHandler.stopAndRemoveAction.bind(this.actionHandler)
            };
        },

        massActionSubmitClick: function ($super) {
            if (this.getSelectedProductsString() == '' || this.getSelectedProductsArray().length == 0) {
                this.alert(TikTokShop.translator.translate('Please select the Products you want to perform the Action on.'));
                return;
            }
            $super();
        },

        // ---------------------------------------

        openPopUp: function (title, content, params, popupId) {
            var self = this;
            params = params || {};
            popupId = popupId || 'modal_view_action_dialog';

            var modalDialogMessage = $(popupId);

            if (!modalDialogMessage) {
                modalDialogMessage = new Element('div', {
                    id: popupId
                });
            }

            modalDialogMessage.innerHTML = '';

            this.popUp = jQuery(modalDialogMessage).modal(Object.extend({
                title: title,
                type: 'slide',
                buttons: [{
                    text: TikTokShop.translator.translate('Cancel'),
                    attr: {id: 'cancel_button'},
                    class: 'action-dismiss',
                    click: function () {
                    }
                }, {
                    text: TikTokShop.translator.translate('Confirm'),
                    attr: {id: 'done_button'},
                    class: 'action-primary action-accept forward',
                    click: function () {
                    }
                }],
                closed: function () {
                    self.selectedProductsIds = [];

                    self.getGridObj().reload();

                    return true;
                }
            }, params));

            this.popUp.modal('openModal');

            try {
                modalDialogMessage.innerHTML = content;
                modalDialogMessage.innerHTML.evalScripts();
            } catch (ignored) {
            }
        }

        // ---------------------------------------
    });

});
