define(['uiElement', 'mage/translate', 'TikTokShop/Plugin/Confirm'], (uiElement, $t, confirm) => {
    'use strict';

    return uiElement.extend({
        defaults: {
            resetBtnTemplate: 'M2E_TikTokShop/listing/unmanaged/button/reset',
            resetBtnLabel: $t('Reset Unmanaged Listings'),
            isShowResetBtn: false,
            urlResetUnmanaged: '',

            enableBtnTemplate: 'M2E_TikTokShop/listing/unmanaged/button/enable',
            enableBtnLabel: $t('Enable Unmanaged Listings Import'),
            isShowSettingsBtn: false,
            urlOpenSettings: '',

            inProgressBtnTemplate: 'M2E_TikTokShop/listing/unmanaged/button/in_progress',
            inProgressBtnLabel: $t('Products import is in progress'),
            isShowInProgressBtn: false,
        },

        openSettings() {
            const win = window.open(this.urlOpenSettings);

            const intervalId = setInterval(() => {
                if (!win.closed) {
                    return;
                }

                clearInterval(intervalId);

                location.reload();

            }, 1000);
        },

        confirmReset() {
            const header = $t('Confirm the Unmanaged Listings reset');
            const content1 = $t('This action will remove all the items from TikTokShop Unmanaged Listings. It will take some time to import them again.');
            const content2 = $t('Do you want to reset the Unmanaged Listings?');

            confirm(
                    {
                        title: '',
                        content: `<h3>${header}</h3><p>${content1}</p><br><p>${content2}</p>`,
                        actions: {
                            confirm: () => {
                                window.setLocation(this.urlResetUnmanaged);
                            },
                            cancel: () => {},
                        },
                    },
            );
        },
    });
});
