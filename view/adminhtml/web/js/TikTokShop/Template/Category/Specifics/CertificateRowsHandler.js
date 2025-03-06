define([
    'jquery',
    'mage/translate'
], function (jQuery, $t) {
    'use strict';

    return function (specificContext) {
        const instance = {
            specific: specificContext,

            init: function () {
                this.addButton();
                this.initAddRowListener();
            },

            addButton: function () {
                jQuery('.M2E-certificate-add-more').each(function (_, element) {
                    const hasMoreClass = jQuery(element).next('.M2E-certificate-variant').length
                            ? ' has_more '
                            : '';
                    const buttonHtml = `
                        <div class="admin__field-control control ${hasMoreClass}">
                            <button type="button" class="action-primary add_row">
                                <span>${$t('Add More')}</span>
                            </button>
                        </div>`;

                    jQuery(element).find('td').first().append(buttonHtml);
                });
            },

            initAddRowListener: function () {
                jQuery('.M2E-certificate-add-more .add_row').on('click', (event) => this.addRow(event));
            },

            addRow: function (event) {
                const idSuffix = Date.now();
                const baseRow = jQuery(event.target).closest('tr');
                baseRow.find('.admin__field-control.control').addClass('has_more');
                const lastVariant = baseRow.nextAll('.M2E-certificate-variant').last();
                const clonedRow = this.initNewRow(baseRow, idSuffix);

                lastVariant.length ? lastVariant.after(clonedRow) : baseRow.after(clonedRow);
                window.initializationCustomAttributeInputs?.();
                this.getModeSelectAttribute(clonedRow, idSuffix).trigger('change');
            },

            initNewRow: function (baseRow, id) {
                let row = baseRow.clone()
                        .removeClass('M2E-certificate-add-more')
                        .addClass('M2E-certificate-variant');

                jQuery(row).find('.admin__field-control.control').remove();
                jQuery(row).find('[name*="[attribute_id]"]').val((_, val) => val + '~' + id);
                jQuery(row).find('[id^="certifications_attributes_dictionary_custom_value_table_"]')
                        .attr('id', (_, name) => name.replace(/_\d+/g, `_${id}`));

                jQuery(row).find('[name*="dictionary_"]').each(function () {
                    jQuery(this)
                            .attr('name', (_, name) => name.replace(/dictionary_\d+/g, `dictionary_${id}`))
                            .attr('id', (_, name) => name.replace(/_\d+/g, `_${id}`));
                });

                jQuery(row).find('[name*="[value_custom_value]"]').val('');
                jQuery(row).find('[name*="[value_custom_attribute]"]')
                        .removeAttr('option_injected')
                        .find('option[value="new-one-attribute"]').remove();

                this.processSelectModeAttribute(row, id);

                return row;
            },

            processSelectModeAttribute: function (row, id) {
                const select = this.getModeSelectAttribute(row, id);
                select.on('change', (event) => {
                    this.specific.dictionarySpecificModeChange(id, event.target);
                });
                select.find('option:first').prop('selected', true);
            },

            getModeSelectAttribute: function (row, idSuffix) {
                return jQuery(row).find('#certifications_attributes_dictionary_value_mode_' + idSuffix);
            },
        };

        instance.init();
    };
});
