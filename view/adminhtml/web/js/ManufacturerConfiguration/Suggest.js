define([
    'jquery',
    'mage/mage',
], function ($) {
    ManufacturerConfigurationTitleSuggest = Class.create({

        initialize: function (inputName, source) {
            if (source.length === 0) {
                return;
            }

            this.initSuggestedComponent(inputName, source)
        },

        initSuggestedComponent: function (inputName, source) {
            const inputSelector = `[name=${inputName}]`;

            $(inputSelector).mage('TikTokShop/Widgets/Suggest', {
                source: source,
                filterProperty: 'label',
                template: this.getTemplate(),
                valueField: inputName,
                submitInputOnEnter: false,
                inputWrapper: '<div class="m2e-suggest-wrapper"></div>',
                dropdownWrapper: '<div class="m2e-suggest-dropdown-wrapper"></div>',
            })
        },

        getTemplate: function () {
            return `
                <% if (data.items.length) { %>
                    <ul data-mage-init='{"menu":[]}'>
                        <% _.each(data.items, function(value) { %>
                            <li <%= data.optionData(value) %>>
                                <a href="#"><%- value.label %></a>
                            </li>
                        <% }); %>
                    </ul>
                <% } %>
            `
        }
    });

    return ManufacturerConfigurationTitleSuggest;
});


