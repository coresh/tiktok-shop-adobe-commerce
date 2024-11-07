define([
    'jquery',
    'TikTokShop/Plugin/Messages',
    'mage/translate',
    'Magento_Ui/js/modal/modal'
], function (jQuery, MessageObj, $t) {

    window.OrderCancellation = Class.create(Common, {

        acceptButtonSelector: null,
        declinetButtonSelector: null,
        declineReasons: [],

        initialize: function (acceptButtonSelector, declinetButtonSelector, declineReasons) {
            this.acceptButtonSelector = acceptButtonSelector;
            this.declinetButtonSelector = declinetButtonSelector;
            this.declineReasons = JSON.parse(declineReasons);

            this.initObservers();
        },

        initObservers: function () {
            jQuery(this.acceptButtonSelector).on('click', this.acceptCancellationRequest.bind(this))
            jQuery(this.declinetButtonSelector).on('click', this.declineCancellationRequest.bind(this))
        },

        acceptCancellationRequest: function () {
            const self = this;
            new Ajax.Request(TikTokShop.url.get('order_cancellation/accept'), {
                method: 'post',
                asynchronous: false,
                onSuccess: function () {
                    window.location.reload();
                }
            });
        },

        declineCancellationRequest: function () {
            this.createDeclineReasonPopup().modal('openModal');
        },

        createDeclineReasonPopup: function () {
            const self = this;
            const popupId = 'decline-reason-popup';

            let reasonPopupContainer = $(popupId);

            if (!reasonPopupContainer) {
                reasonPopupContainer = new Element('div', {id: popupId});
            }

            reasonPopupContainer.innerHTML = '';

            const cancelButton = {
                text: $t('Cancel'),
                class: 'action-secondary action-dismiss',
                click: function () {
                    popUp.modal('closeModal');
                }
            }

            const declineButton = {
                text: $t('Decline'),
                class: 'action-primary action-accept',
                click: function () {
                    new Ajax.Request(TikTokShop.url.get('order_cancellation/decline'), {
                        method: 'post',
                        parameters: {
                            decline_reason: jQuery('#decline-reason-select').val()
                        },
                        asynchronous: false,
                        onSuccess: function (transport) {
                            popUp.modal('closeModal');
                            window.location.reload();;
                        }
                    });
                }
            }

            const popUp = jQuery(reasonPopupContainer).modal({
                type: 'popup',
                title: $t('Decline Reason'),
                modalClass: 'width-50',
                buttons: [cancelButton, declineButton]
            });

            reasonPopupContainer.insert(this.makeDeclineReasonPopupContent());

            return popUp;
        },

        makeDeclineReasonPopupContent: function () {

            let options = '';
            this.declineReasons.forEach(function (option) {
                options += `<option value="${option['value']}">${option['label']}</option>`
            });

            return `<div id="decline-reason-modal">
                        <div class="admin__field field">
                            <div class="admin__field-control control" style="text-align:center;">
                                <select id="decline-reason-select" class="select admin__control-select">
                                    ${options}
                                </select>
                            </div>
                        </div>
                    </div>`
        },
    });
});
