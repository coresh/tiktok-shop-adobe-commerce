define([
    'TikTokShop/Common'
], function () {

    window.TemplateManager = Class.create(Common, {

        // ---------------------------------------

        initialize: function () {
        },

        // ---------------------------------------

        checkMessages: function (id, nick, data, storeId, container, callback, shopId = null) {
            var parameters = '';

            parameters += 'id=' + encodeURIComponent(id);
            parameters += '&nick=' + encodeURIComponent(nick);
            parameters += '&store_id=' + encodeURIComponent(storeId);
            parameters += '&shop_id=' + encodeURIComponent(shopId);
            parameters += '&' + data;

            new Ajax.Request(TikTokShop.url.get('templateCheckMessages'), {
                method: 'post',
                asynchronous: true,
                parameters: parameters,
                onSuccess: function (transport) {

                    var messages = transport.responseText.evalJSON()['messages'];

                    if (messages.length == 0) {
                        $(container).innerHTML = '';
                        return;
                    }

                    $(container).innerHTML = messages;

                    if (typeof callback == 'function') {
                        callback();
                    }

                }.bind(this)
            });
        }

        // ---------------------------------------
    });

});
