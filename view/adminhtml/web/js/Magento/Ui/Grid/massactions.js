define([
    'Magento_Ui/js/grid/massactions',
    'jquery'
], function (Massactions, $) {
    'use strict';

    return Massactions.extend({
        defaultCallback: function (action, data) {
            $("body").trigger('processStart');

            this._super();
        }
    });
});
