define([
    'TikTokShop/Common'
], function () {
    window.ManufacturerConfigurationActionButtons = Class.create(Common, {
        gridObj: undefined,

        initialize: function (gridObj) {
            this.gridObj = gridObj
        },

        addNew: function (url) {
            this.openWindow(url, () => window[this.gridObj].reload());
        }
    });
});
