define([
    'jquery',
    'mage/translate',
    'M2ECore/Plugin/Confirm',
    'TikTokShop/Listing/View/Action'
], function ($, $t, confirm) {
    'use strict';

    window.SellOnAnotherMarketAction = Class.create(ListingViewAction, {

        choiceListingButtonSelector: '.choice-listing',

        modal: null,
        isModalObserversInitialized: false,

        initialize: function ($super, gridHandler) {
            $super(gridHandler);

            this.setProgressBar('listing_view_progress_bar')
            this.setGridWrapper('listing_view_content_container')
        },

        handle: function () {

            this.initModal();

            new Ajax.Request(TikTokShop.url.get('sellOnAnotherMarker/selectListing'), {
                method: 'post',
                asynchronous: true,
                onSuccess: (transport) => {
                    this.setContentToModal(transport.responseText)
                    this.initModalObservers();
                    this.openModal();
                },
            });
        },

        initModal: function () {
            if (this.modal !== null) {
                return;
            }

            this.modal = $('<div id="sell_on_another_market_modal">');

            this.modal.modal({
                title: $t('Sell on Another Market'),
                type: 'popup',
                buttons: [
                    {
                        text: $t('Cancel'),
                        class: 'action-secondary action-dismiss',
                        click: () => this.closeModal()
                    },
                    {
                        text: $t('Create New Listing'),
                        class: 'action-primary action-accept',
                        click: () => this.createNewListing()
                    }
                ],
            });
        },

        setContentToModal: function (content) {
            this.modal.html('');
            this.modal.html(content);
        },

        openModal: function () {
            this.modal.modal('openModal')
        },

        closeModal: function () {
            this.modal.modal('closeModal')
        },

        initModalObservers: function () {
            if (this.isModalObserversInitialized) {
                return;
            }

            $(document).on('click', this.choiceListingButtonSelector, (event) => {
                const sourceListingId = $(event.currentTarget).attr('data-source-listing-id');
                const targetListingId = $(event.currentTarget).attr('data-target-listing-id');

                confirm({
                    title: $t('Are you sure?'),
                    content: null,
                    actions: {
                        confirm: () => this.moveProducts(sourceListingId, targetListingId),
                        cancel: () => false,
                    },
                });
            });

            this.isModalObserversInitialized = true;
        },

        moveProducts: function (sourceListingId, targetListingId) {
            this.closeModal();

            const selectedProductsParts = this.gridHandler.getSelectedItemsParts(1);
            if (selectedProductsParts.length === 0) {
                return;
            }

            this.sendParts(selectedProductsParts, selectedProductsParts.length, {
                "source_listing_id": sourceListingId,
                "target_listing_id": targetListingId,
            });
        },

        sendParts: function (parts, totalPartsCount, requestParams, results = []) {
            if (parts.length === totalPartsCount) {
                this.beforeSendParts();
            }

            if (parts.length === 0) {
                this.afterSendParts(results, requestParams["target_listing_id"]);
                return;
            }

            const part = parts.shift();

            requestParams["selected_products"] = part.join(',');

            new Ajax.Request(TikTokShop.url.get('sellOnAnotherMarker/moveProducts'), {
                method: 'post',
                asynchronous: true,
                parameters: requestParams,
                onSuccess: (transport)=> {

                    results.push(JSON.parse(transport.responseText));

                    this.aroundSendParts(totalPartsCount, parts.length);
                    setTimeout(() => this.sendParts(parts, totalPartsCount, requestParams, results), 500)
                },
                onFailure: (transport) => {
                    this.stopProgress()
                    this.messageObj.addError(transport.statusText)
                    console.error(transport.responseText)
                }
            });
        },

        beforeSendParts: function () {
            this.messageObj.clear();
            $(this.errorsSummaryContainerId).hide();
            this.progressBarObj.reset();
            this.progressBarObj.show($t('Sell on Another Market'));
            this.gridWrapperObj.lock();
            $('.loading-mask').css('visibility', 'hidden');
        },

        aroundSendParts: function (totalPartsCount, partsLength) {
            const percents = (100 / totalPartsCount) * (totalPartsCount - partsLength);

            if (percents <= 0) {
                this.progressBarObj.setPercents(0, 0);
            } else if (percents >= 100) {
                this.progressBarObj.setPercents(100, 0);
            } else {
                this.progressBarObj.setPercents(percents, 1);
            }
        },

        afterSendParts: function (results, targetListingId) {
            this.stopProgress();
            this.processResults(results, targetListingId)
        },

        processResults: function (results, targetListingId) {
            let statuses = results
                    .map((e) => e.result)
                    .filter((value, index, array) => array.indexOf(value) === index);

            if (statuses.length === 1) {
                if (statuses[0] === 'success') {
                    this.messageObj.addSuccess($t('"Sell on Another Market" task has completed.'));
                    this.redirectToTargetListing(targetListingId)
                    return;
                } else if (statuses[0] === 'error') {
                    this.messageObj.addError($t('"Sell on Another Market" task has completed with errors.'));
                    return;
                }
            }

            this.messageObj.addWarning($t('"Sell on Another Market" task has completed with warnings.'));
            this.redirectToTargetListing(targetListingId)
        },

        stopProgress: function () {
            this.progressBarObj.hide();
            this.progressBarObj.reset();
            this.gridWrapperObj.unlock();
            $('.loading-mask').css('visibility', 'visible');
        },

        redirectToTargetListing: function (targetListingId) {
            let url = TikTokShop.url.get('sellOnAnotherMarker/targetListingUrl');
            url += 'id/' +targetListingId

            setTimeout(() => setLocation(url), 1000);
        },

        createNewListing: function () {
            let newListingUrl = TikTokShop.url.get('sellOnAnotherMarker/createNewListing')

            const createListingWindow = window.open(newListingUrl);

            const interval = setInterval(() => {
                if (!createListingWindow.closed) {
                    return;
                }

                clearInterval(interval);

                const jsGridObjName = $('input#grid_object').val();
                if (window.hasOwnProperty(jsGridObjName)) {
                    window[jsGridObjName].reload()
                } else {
                    console.error('Property ' + jsGridObjName + ' not found in window')
                }
            }, 1000);
        }
    });
});
