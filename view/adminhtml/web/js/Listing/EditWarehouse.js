define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function (jQuery, confirm, modal, $t) {
    const WAREHOUSE_FORM_ID = 'edit_warehouse_form';

    window.ListingEditListingWarehouse = Class.create({
        initialize: function (listingId) {
            this.listingId = listingId;
        },

        openPopup: function (id = null) {
            const listingId = id ?? this.listingId;

            new Ajax.Request(TikTokShop.url.get('listing/edit/warehouse'), {
                method: 'GET',
                parameters: {id: listingId},
                onSuccess: this.handleWarehouseFormLoad.bind(this),
            });
        },

        handleWarehouseFormLoad: function (transport) {
            this.removeExistingForm();
            this.insertWarehouseForm(transport.responseText);
            this.initializeModal();
        },

        removeExistingForm: function () {
            const existingForm = $(WAREHOUSE_FORM_ID);
            if (existingForm) {
                existingForm.remove();
            }
        },

        insertWarehouseForm: function (formHtml) {
            $('html-body').insert({bottom: formHtml});
        },

        initializeModal: function () {
            const form = jQuery(`#${WAREHOUSE_FORM_ID}`);

            modal({
                title: $t('Edit Listing Warehouse'),
                type: 'popup',
                modalClass: 'width-50',
                buttons: [
                    this.getCancelButtonConfig(form),
                    this.getSaveButtonConfig(form),
                ],
            }, form);

            form.modal('openModal');
        },

        getCancelButtonConfig: function (form) {
            return {
                text: $t('Cancel'),
                class: 'action-secondary action-dismiss',
                click: () => form.modal('closeModal'),
            };
        },

        getSaveButtonConfig: function (form) {
            const warehouseIdElement = form.find('#warehouse_id');
            const selectedOption = warehouseIdElement.length ? warehouseIdElement.find('option[selected]') : null;
            const initialWarehouseValue = selectedOption ? selectedOption.val() : null;

            return {
                text: $t('Save'),
                class: 'action-primary action-accept',
                click: () => this.handleSaveButtonClick(form, initialWarehouseValue),
            };
        },

        handleSaveButtonClick: function (form, initialWarehouseValue) {
            const warehouseIdElement = form.find('#warehouse_id');
            const currentWarehouseValue = warehouseIdElement.length ? warehouseIdElement.val() : null;

            if (currentWarehouseValue === initialWarehouseValue || !currentWarehouseValue) {
                form.modal('closeModal');

                return false;
            }

            this.saveListingWarehouse();
        },

        saveListingWarehouse: function () {
            const form = jQuery(`#${WAREHOUSE_FORM_ID}`);
            if (!form.valid()) {
                return false;
            }

            confirm({
                content: $t('Are you sure?'),
                actions: {
                    confirm: () => this.submitForm(),
                    cancel: () => form.modal('closeModal'),
                },
            });
        },

        submitForm: function () {
            const form = jQuery(`#${WAREHOUSE_FORM_ID}`);
            new Ajax.Request(TikTokShop.url.get('listing/edit/saveWarehouse'), {
                method: 'POST',
                parameters: form.serialize(),
                onSuccess: () => {
                    form.modal('closeModal');
                    location.reload();
                },
            });
        },
    });
});
