define([
    'jquery',
    'TikTokShop/Url',
    'TikTokShop/Php',
    'TikTokShop/Translator',
    'TikTokShop/Common',
    'prototype',
    'TikTokShop/Plugin/BlockNotice',
    'TikTokShop/Plugin/Prototype/Event.Simulate',
    'TikTokShop/Plugin/Fieldset',
    'TikTokShop/Plugin/Validator',
    'TikTokShop/General/PhpFunctions',
    'mage/loader_old'
], function (jQuery, Url, Php, Translator) {

    jQuery('body').loader();

    Ajax.Responders.register({
        onException: function (event, error) {
            console.error(error);
        }
    });

    return {
        url: Url,
        php: Php,
        translator: Translator
    };

});
