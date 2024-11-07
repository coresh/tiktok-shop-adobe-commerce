define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'TikTokShop/Plugin/Messages',
    'TikTokShop/Action',
    'TikTokShop/Plugin/ProgressBar',
    'TikTokShop/Plugin/AreaWrapper',
], function ($, $t, modal, MessagesObj) {
    window.MovingFromListing = Class.create(Action, {

        // ---------------------------------------

        setProgressBar: function (progressBarId) {
            this.progressBarObj = new ProgressBar(progressBarId);
        },

        setGridWrapper: function (wrapperId) {
            this.wrapperObj = new AreaWrapper(wrapperId);
        },

        // ---------------------------------------

        run: function () {
            this.getGridHtml(this.gridHandler.getSelectedProductsArray());
        },

        // ---------------------------------------

        openPopUp: function (gridHtml, popup_title, buttons) {
            const self = this;

            if (typeof buttons === 'undefined') {
                buttons = [{
                    class: 'action-secondary action-dismiss',
                    text: $t('Cancel'),
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: $t('Add New Listing'),
                    class: 'action-primary action-accept',
                    click: function () {
                        self.startListingCreation(TikTokShop.url.get('add_new_listing_url'));
                    }
                }];
            }

            let modalDialogMessage = $('move_modal_dialog_message');

            if (modalDialogMessage) {
                modalDialogMessage.remove();
            }

            modalDialogMessage = new Element('div', {
                id: 'move_modal_dialog_message'
            });

            modalDialogMessage.update(gridHtml);

            this.popUp = $(modalDialogMessage).modal({
                title: popup_title,
                type: 'popup',
                buttons: buttons
            });

            this.popUp.modal('openModal');
        },

        // ---------------------------------------

        getGridHtml: function (selectedProducts) {
            this.selectedProducts = selectedProducts;
            this.gridHandler.unselectAll();
            MessagesObj.clear();
            $('listing_container_errors_summary').hide();

            this.progressBarObj.reset();
            this.progressBarObj.setTitle('Preparing for Product Moving');
            this.progressBarObj.setStatus('Products are being prepared for Moving. Please wait…');
            this.progressBarObj.show();
            this.scrollPageToTop();

            $$('.loading-mask').invoke('setStyle', {visibility: 'hidden'});
            this.wrapperObj.lock();

            let productsByParts = this.makeProductsParts();
            this.prepareData(productsByParts, productsByParts.length, 1);
        },

        makeProductsParts: function () {
            const self = this;

            let productsInPart = 500;
            let parts = [];

            if (self.selectedProducts.length < productsInPart) {
                let part = [];
                part[0] = self.selectedProducts;
                return parts[0] = part;
            }

            let result = [];
            for (let i = 0; i < self.selectedProducts.length; i++) {
                if (result.length === 0 || result[result.length - 1].length === productsInPart) {
                    result[result.length] = [];
                }
                result[result.length - 1][result[result.length - 1].length] = self.selectedProducts[i];
            }

            return result;
        },

        prepareData: function (parts, partsCount, isFirstPart) {
            const self = this;

            if (parts.length === 0) {
                return;
            }

            let isLastPart = parts.length === 1 ? 1 : 0;
            let part = parts.splice(0, 1);
            let currentPart = part[0];

            $.ajax(
                    {
                        url: TikTokShop.url.get('prepareData'),
                        type: 'POST',
                        data: {
                            is_first_part: isFirstPart,
                            is_last_part: isLastPart,
                            products_part: implode(',', currentPart)
                        },
                        dataType: 'json',
                        success: function (response) {
                            let percents = (100 / partsCount) * (partsCount - parts.length);

                            if (percents <= 0) {
                                self.progressBarObj.setPercents(0, 0);
                            } else if (percents >= 100) {
                                self.progressBarObj.setPercents(100, 0);
                                self.progressBarObj.setStatus('Products are almost prepared for Moving...');
                            } else {
                                self.progressBarObj.setPercents(percents, 1);
                            }

                            if (!response.result) {
                                self.completeProgressBar();
                                if (typeof response.message !== 'undefined') {
                                    MessagesObj.addError(response.message);
                                }
                                return;
                            }

                            if (isLastPart) {
                                self.moveToListingGrid();
                                return;
                            }

                            setTimeout(function () {
                                self.prepareData(parts, partsCount, 0);
                            }, 500);
                        },
                    }
            );
        },

        moveToListingGrid: function () {
            let self = this;
            let ignoreListings = JSON.parse(TikTokShop.customData.ignoreListings);
            $.ajax(
                    {
                        url: TikTokShop.url.get('moveToListingGridHtml'),
                        type: 'GET',
                        data: {
                            ignoreListing: ignoreListings[0]
                        },
                        success: function (response) {
                            self.completeProgressBar();
                            self.openPopUp(response, $t('Moving TikTokShop Items'));
                        },
                    }
            );
        },

        // ---------------------------------------

        submit: function (listingId, onSuccess) {
            const self = this;

            $$('.loading-mask').invoke('setStyle', {visibility: 'visible'});

            $.ajax(
                    {
                        url: TikTokShop.url.get('moveToListing'),
                        type: 'POST',
                        data: {
                            listingId: listingId
                        },
                        dataType: 'json',
                        success: function (response) {
                            self.popUp.modal('closeModal');
                            self.scrollPageToTop();

                            $$('.loading-mask').invoke('setStyle', {visibility: 'hidden'});
                            if (response.result) {
                                onSuccess.bind(self.gridHandler)(listingId);
                                if (response.isFailed) {
                                    if (response.message) {
                                        MessagesObj.addError(response.message);
                                    }
                                } else {
                                    if (response.message) {
                                        MessagesObj.addSuccess(response.message);
                                    }
                                }
                                return;
                            }

                            self.gridHandler.unselectAllAndReload();
                            if (response.message) {
                                MessagesObj.addError(response.message);
                            }
                        },
                    }
            );
        },

        // ---------------------------------------

        startListingCreation: function (url, response) {
            let win = window.open(url);

            let intervalId = setInterval(function () {
                if (!win.closed) {
                    return;
                }

                clearInterval(intervalId);
                listingSettingsMovingGridJsObject.reload();
            }, 1000);
        },

        // ---------------------------------------

        completeProgressBar: function () {
            this.progressBarObj.hide();
            this.progressBarObj.reset();
            this.wrapperObj.unlock();
            $$('.loading-mask').invoke('setStyle', {visibility: 'hidden'});
        }

        // ---------------------------------------
    });
});
