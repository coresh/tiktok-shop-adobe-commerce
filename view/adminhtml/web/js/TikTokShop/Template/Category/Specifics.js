define([
    'jquery',
    'mage/translate',
    'TikTokShop/TikTokShop/Template/Category/Specifics/CertificateRowsHandler',
    'TikTokShop/Common'
], function (jQuery, $t, CertificateRowsHandler) {
    window.TikTokShopTemplateCategorySpecifics = Class.create(Common, {

        maxSelectedSpecifics: 45,
        specificsSnapshot: {},

        // ---------------------------------------

        initialize: function () {
            jQuery.validator.addMethod('tiktokshop-custom-specific-attribute-id', function (value, el) {

                        var customTitleInput = el;

                        var result = true;
                        $$('.tiktokshop-dictionary-specific-attribute-id').each(function (el) {
                            if (el.value == value) {
                                result = false;
                                throw $break;
                            }
                        });

                        $$('.tiktokshop-custom-specific-attribute-id').each(function (el) {
                            if (el == customTitleInput) {
                                return;
                            }

                            if (!el.visible()) {
                                return;
                            }

                            if (trim(el.value) == value) {
                                result = false;
                                throw $break;
                            }
                        });

                        return result;
                    }, $t('Item Specifics cannot have the same Labels.')
            );

            this.createSpecificsSnapshot();

            new CertificateRowsHandler(this);
        },

        getElementScope: function (element) {
            return jQuery(element)
                    .parents('table')
                    .attr('data-specific-scope');
        },

        // ---------------------------------------

        resetSpecifics: function () {
            $$('.input-specific-value-mode').each(function (el) {
                el.childElements()[0].selected = true;
                el.simulate('change');
            });

            $$('.remove_custom_specific_button').each(function (el) {
                el.simulate('click');
            });
        },

        saveAndEditClick: function (url)
        {
            this.submitForm(url);
            this.validateSpecific();
        },

        saveAndCloseClick: function (url)
        {
            this.submitForm(url);
            this.validateSpecific();
        },

        validateSpecific: function () {
            let dictionaryId = jQuery('input#dictionary_id').val();
            window.TikTokShopCategoryAttributeValidationPopup.setTemplateCategoryId(dictionaryId);
        },

        // ---------------------------------------

        createSpecificsSnapshot: function () {
            this.specificsSnapshot = this.collectSpecifics();
        },

        isSpecificsChanged: function () {
            return JSON.stringify(this.specificsSnapshot) !== JSON.stringify(this.collectSpecifics());
        },

        collectSpecifics: function () {
            let specifics = {};

            let self = this;
            $$('.specific-table').each(function (table, index) {
                let attributeScope = table.getAttribute('data-specific-scope');
                if (typeof specifics[attributeScope] === 'undefined') {
                    specifics[attributeScope] = {};
                }
                table.select('.collected-attribute').each(function (collectedItem) {
                    if (collectedItem.disabled) {
                        return true;
                    }

                    let temp = collectedItem.name.match(/\[([a-z0-9_]*)\]\[([a-z_]*)\]/);
                    if (typeof specifics[attributeScope][temp[1]] === 'undefined') {
                        specifics[attributeScope][temp[1]] = {};
                    }

                    if (typeof specifics[attributeScope][temp[1]][temp[2]] === 'undefined') {
                        specifics[attributeScope][temp[1]][temp[2]] = {};
                    }

                    if (collectedItem.multiple) {
                        specifics[attributeScope][temp[1]][temp[2]] = self.getSelectValues(collectedItem);
                    } else {
                        let specific = specifics[attributeScope][temp[1]]['value_custom_value'];
                        if (typeof specific !== 'undefined' && Object.keys(specific).length !== 0) {
                            let multi_input = [];
                            if (Object.isArray(specific)) {
                                specifics[temp[1]][temp[2]].forEach(function (item) {
                                    multi_input.push(item);
                                });
                            } else {
                                multi_input.push(specifics[attributeScope][temp[1]][temp[2]]);
                            }
                            multi_input.push(collectedItem.value);
                            specifics[attributeScope][temp[1]][temp[2]] = multi_input;
                        } else {
                            specifics[attributeScope][temp[1]][temp[2]] = collectedItem.value;
                        }
                    }
                });
            });

            return specifics;
        },

        getSelectValues: function (select) {
            let result = [];
            let options = select && select.options;
            let opt;

            for (let i = 0, iLen = options.length; i < iLen; i++) {
                opt = options[i];

                if (opt.selected) {
                    result.push(opt.value || opt.text);
                }
            }

            return result;
        },

        // ---------------------------------------

        // dictionary specifics
        // ---------------------------------------

        dictionarySpecificModeChange: function (index, select) {
            let scope = this.getElementScope(select);

            let recommended = $(`${scope}_dictionary_value_tiktokshop_recommended_${index}`),
                    customValueTable = $(`${scope}_dictionary_custom_value_table_${index}`),
                    customValueInputs = $$(`[id=${scope}_dictionary_value_custom_value_${index}]`),
                    attribute = $(`${scope}_dictionary_value_custom_attribute_${index}`);

            recommended.hide().disable();
            customValueTable.hide();
            customValueInputs.invoke('disable');
            attribute.hide().disable();

            if (select.value == TikTokShop.php.constant('\\M2E\\TikTokShop\\Model\\TikTokShop\\Template\\Category::VALUE_MODE_TTS_RECOMMENDED')) {
                recommended.show().enable();
            }
            if (select.value == TikTokShop.php.constant('\\M2E\\TikTokShop\\Model\\TikTokShop\\Template\\Category::VALUE_MODE_CUSTOM_VALUE')) {
                customValueTable.show();
                customValueInputs.invoke('enable');
            }
            if (select.value == TikTokShop.php.constant('\\M2E\\TikTokShop\\Model\\TikTokShop\\Template\\Category::VALUE_MODE_CUSTOM_ATTRIBUTE')) {
                attribute.show().enable();
            }
        },

        addItemSpecificsCustomValueRow: function (index, button) {
            let scope = this.getElementScope(button);

            let timestampId = new Date().getTime();
            let tbody = $(`${scope}_dictionary_custom_value_table_body_${index}`);

            let newRow = Element.clone(tbody.childElements()[0], true);
            newRow
                    .down('button.remove_item_specifics_custom_value_button')
                    .addEventListener('click', this.removeItemSpecificsCustomValue.bind(this));

            let newRowInput = newRow.select('[id*=_value_custom_value_' + index + '_]')[0];
            newRowInput.clear();

            //replacing id to unique value
            newRowInput.setAttribute('id', newRowInput.id.replace(/_\d+$/, '_' + timestampId));

            tbody.appendChild(newRow);

            let valuesCounter = tbody.childElements().length;

            if (parseInt(tbody.getAttribute('data-max_values')) > valuesCounter) {
                button.show();
            } else {
                button.hide();
            }

            if (parseInt(tbody.getAttribute('data-min_values')) >= valuesCounter) {
                $$(`[id$=custom_value_table_body_${index}] tr td.btn_value_remove`).invoke('hide');
            } else {
                $$(`[id$=custom_value_table_body_${index}] tr td.btn_value_remove`).invoke('show');
            }
        },

        removeItemSpecificsCustomValue: function (button) {
            if (button instanceof PointerEvent) {
                button = button.currentTarget;
            }

            let tbody = $(button).up('tbody');
            let addBtn = $(button).up('table').next('a');

            $(button).up('tr').remove();

            let valuesCounter = tbody.childElements().length;

            if (parseInt(tbody.getAttribute('data-max_values')) > valuesCounter) {
                addBtn.show();
            } else {
                addBtn.hide();
            }

            if (valuesCounter === 1 || parseInt(tbody.getAttribute('data-min_values')) >= valuesCounter) {
                let btnRemove = tbody.getElementsByClassName('btn_value_remove');
                for (let i = 0; i < btnRemove.length; i++) {
                    btnRemove[i].hide();
                }
            }
        },
    });
});
