define([
    'jquery',
    'TikTokShop/Plugin/Messages',
    'mage/translate',
    'Magento_Ui/js/modal/modal'
], function ($, MessageObj, $t, modal) {
    'use strict';

    return function (options) {
        const acceptSelector = options.acceptSelector;
        const declineSelector = options.declineSelector;
        const acceptUrl = options.acceptUrl;
        const declineUrl = options.declineUrl;
        const getDeclineReasonsUrl = options.getDeclineReasonsUrl;

        $(acceptSelector).on('click', function () {
            new Ajax.Request(acceptUrl, {
                method: 'post',
                asynchronous: false,
                onSuccess: function () {
                    window.location.reload();
                }
            });
        });

        $(declineSelector).on('click', function () {
            new Ajax.Request(getDeclineReasonsUrl, {
                method: 'get',
                onSuccess: function (transport) {
                    let declineReasons = {};

                    try {
                        const json = JSON.parse(transport.responseText);
                        declineReasons = json.reasons;
                    } catch (e) {
                        console.error('Failed to parse decline reasons JSON:', e);
                        return;
                    }

                    createDeclineReasonPopup(declineReasons).modal('openModal');
                }
            });
        });

        function createDeclineReasonPopup(declineReasons) {
            const popupId = 'decline-reason-popup';
            let reasonPopupContainer = document.getElementById(popupId);

            if (!reasonPopupContainer) {
                reasonPopupContainer = document.createElement('div');
                reasonPopupContainer.id = popupId;
                document.body.appendChild(reasonPopupContainer);
            }

            reasonPopupContainer.innerHTML = '';

            const cancelButton = {
                text: $t('Cancel'),
                class: 'action-secondary action-dismiss',
                click: function () {
                    popUp.modal('closeModal');
                }
            };

            const declineButton = {
                text: $t('Decline'),
                class: 'action-primary action-accept',
                click: function () {
                    let declineReasonsArr = [];

                    $('select.decline-reason-select').each(function () {
                        const returnId = $(this).data('return-id');
                        const selectedName = $(this).val();

                        if (returnId && selectedName) {
                            declineReasonsArr.push({
                                return_id: returnId,
                                reason: selectedName
                            });
                        }
                    });

                    new Ajax.Request(declineUrl, {
                        method: 'post',
                        parameters: {
                            decline_reasons: JSON.stringify(declineReasonsArr)
                        },
                        asynchronous: false,
                        onSuccess: function () {
                            popUp.modal('closeModal');
                            window.location.reload();
                        }
                    });
                }
            };

            const popUp = $(reasonPopupContainer).modal({
                type: 'popup',
                title: $t('Decline Reason'),
                modalClass: 'width-50',
                buttons: [cancelButton, declineButton]
            });

            reasonPopupContainer.innerHTML = makeDeclineReasonPopupContent(declineReasons);

            return popUp;
        }

        function makeDeclineReasonPopupContent(declineReasons) {
            let content = '';

            Object.entries(declineReasons).forEach(([title, reasonsByReturnId]) => {
                Object.entries(reasonsByReturnId).forEach(([returnId, reasonList]) => {
                    let options = '';

                    reasonList.forEach(reason => {
                        if (reason && reason.name && reason.text) {
                            options += `<option value="${reason.name}">${reason.text}</option>`;
                        }
                    });

                    content += `
                        <div class="decline-reason-block" style="margin-bottom: 20px;">
                            <div class="admin__field field" style="display:flex; align-items:center;gap:10px; justify-content:center;">
                                <label for="decline-reason-select-${returnId}" class="admin__field-label">${title}:</label>
                                <select
                                    id="decline-reason-select-${returnId}"
                                    class="select admin__control-select decline-reason-select"
                                    data-return-id="${returnId}"
                                    style="min-width: 250px;">
                                    ${options}
                                </select>
                            </div>
                        </div>
                    `;
                });
            });

            return `<div id="decline-reason-modal">${content}</div>`;
        }
    };
});
