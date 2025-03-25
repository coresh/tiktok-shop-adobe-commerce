define([
    'mage/translate',
    'M2ECore/Plugin/Confirm',
    'TikTokShop/Common'
], function ($t, confirm) {
    window.ManufacturerConfigurationGrid = Class.create(Common, {
        editUrl: undefined,
        deleteUrl: undefined,
        gridObject: undefined,

        initialize: function (config) {
            this.editUrl = config['editUrl'];
            this.deleteUrl = config['deleteUrl'];
            this.gridObject = window[config['gridJsName']];
        },

        actionEdit: function (id) {
            const editUrl = this.editUrl + 'id/' + id;
            this.openWindow(editUrl, () => this.gridObject.reload())
        },

        actionDelete: function (id) {
            confirm({
                title: $t('Are you sure?'),
                content: null,
                actions: {
                    confirm: () => this.deleteRecord(id),
                    cancel: () => false,
                },
            });
        },

        deleteRecord: function (id) {
            new Ajax.Request(this.deleteUrl, {
                method: 'post',
                parameters: { id: id},
                onSuccess: (transport) => this.gridObject.reload(),
                onFailure: (transport) => {
                    console.error(transport.responseText)
                }
            });
        }
    });
});
