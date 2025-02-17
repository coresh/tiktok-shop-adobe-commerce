define([
    'jquery',
    'mage/translate',
    'TikTokShop/Plugin/Messages',
], function($, $t, MessagesObj) {

    window.TikTokShopTemplateCompliance = Class.create({
        wrapperSelector: '#magento_block_template_compliance_edit_form',
        addRowButtonSelector: '.add_row',
        removeRowButtonSelector: '.remove_row',
        popupResponsiblePersonsSelector: '.manufacturer_details',
        refreshResponsiblePersonsSelector: '.refresh_responsible_persons',

        accounts: null,
        selectedAccountId: null,
        manufacturerId: null,
        responsiblePersonIds: null,
        urlGetResponsiblePersons: '',
        urlGetResponsiblePersonPopupHtml: '',
        urlGetResponsiblePersonUpdate: '',
        urlGetManufacturers: '',
        urlGetManufacturerPopupHtml: '',
        urlManufactureUpdate: '',
        currentManufacturerTitle: null,
        currentResponsiblePersonTitle: null,

        initialize: function(config) {
            this.urlGetResponsiblePersons = config.urlGetResponsiblePersons;
            this.urlGetResponsiblePersonPopupHtml = config.urlGetResponsiblePersonPopupHtml;
            this.urlGetResponsiblePersonUpdate = config.urlGetResponsiblePersonUpdate;
            this.urlGetManufacturers = config.urlGetManufacturers;
            this.urlGetManufacturerPopupHtml = config.urlGetManufacturerPopupHtml;
            this.urlManufactureUpdate = config.urlManufactureUpdate;

            this.setAccountId(config.accountId);
            this.setManufactureId(config.manufacturerId);
            this.setResponsiblePersonIds(config.responsiblePersonIds);

            this.initAccount();
            this.initObservers();
        },

        // ----------------------------------------

        initObservers: function () {
            this.initResponsiblePersonLinks($(this.popupResponsiblePersonsSelector));
            this.initRefreshResponsiblePersons($(this.refreshResponsiblePersonsSelector));

            this.initAddPerson($(this.addRowButtonSelector));
            this.initRemovePerson($(this.removeRowButtonSelector));
        },

        initAddPerson: function (element) {
            const self = this;

            element.on('click', function (event) {
                self.addRow(event);
            });
        },

        initRemovePerson: function (element) {
            const self = this;

            $(element).on('click', function (event) {
                self.removeRow(event);
            });
        },

        initRefreshResponsiblePersons: function (element) {
            const self = this;

            $(element).on('click', function () {
                self.updateResponsiblePersons(true);
            });
        },

        initResponsiblePersonLinks: function (element) {
            const self = this;

            $(element).on('click', function (event) {
                let {index, isNew} = $(event.target).data();
                self.loadResponsiblePersonPopup(Boolean(isNew), index);
            });
        },

        initAccount: function() {
            const self = this;

            $('#account_id').on('change', function() {
                self.setAccountId($('#account_id').val() || self.selectedAccountId);
            });
        },

        hasAccountId: function() {
            return this.accountId !== null;
        },

        setAccountId: function(id) {
            this.accountId = parseInt(id) || null;

            if (this.hasAccountId()) {
                $('#refresh_manufacturer, #refresh_responsible_persons').show();
                $('.actions').show();
                $('.add_row_wrapper').show();

                this.loadAccountData();
            }
        },

        getAccountId: function() {
            return this.accountId;
        },

        loadAccountData: function() {
            this.updateManufacturers(false);
            this.updateResponsiblePersons(false);
        },

        // ----------------------------------------

        hasManufactureId: function() {
            return this.manufacturerId !== null;
        },

        setManufactureId: function(id) {
            this.manufacturerId = id || null;
        },

        getManufactureId: function() {
            return this.manufacturerId;
        },

        updateManufacturers: function(isForce) {
            const self = this;

            new Ajax.Request(this.urlGetManufacturers, {
                method: 'post',
                parameters: {account_id: self.getAccountId(), force: isForce ? 1 : 0},
                onSuccess: function(transport) {
                    const response = JSON.parse(transport.responseText);
                    if (response.result) {
                        self.renderManufacturers(
                                response.manufacturers.each(function(manufacture) {
                                    return {
                                        'id': manufacture.id,
                                        'title': manufacture.title,
                                    };
                                }),
                        );

                        return;
                    }

                    console.error(response.message);
                },
            });
        },

        renderManufacturers: function(manufacturers) {
            const select = $('#manufacturer_id');
            select.find('option').remove();

            manufacturers.each(function(manufacturer) {
                select.append(new Option(manufacturer.title, manufacturer.id));
            });

            if (this.hasManufactureId()) {
                select.val(this.getManufactureId());
            }
        },

        // ----------------------------------------

        hasResponsiblePersonId: function() {
            return this.responsiblePersonIds !== null;
        },

        setResponsiblePersonIds: function(ids) {
            if (ids) {
                this.responsiblePersonIds = JSON.parse(ids);
            }
        },

        getResponsiblePersonIds: function() {
            return this.responsiblePersonIds;
        },

        updateResponsiblePersons: function(isForce) {
            const self = this;

            new Ajax.Request(this.urlGetResponsiblePersons, {
                method: 'post',
                parameters: {account_id: self.getAccountId(), force: isForce ? 1 : 0},
                onSuccess: function(transport) {
                    const response = JSON.parse(transport.responseText);
                    if (response.result) {
                        self.renderResponsiblePersons(
                                response.responsiblePersons.each(function(person) {
                                    return {
                                        'id': person.id,
                                        'title': person.title,
                                    };
                                }),
                        );

                        return;
                    }

                    console.error(response.message);
                },
            });
        },

        renderResponsiblePersons: function(persons) {
            const self = this;

            let ids = self.getResponsiblePersonIds();
            const selects = $("[id^='responsible_person_id_']");

            let options = persons.map(function(person) {
                return new Option(person.title, person.id);
            });

            if (selects.length > 1) {
                selects.each(function(select) {
                    let selectElement = $(select);
                    select.find('option').remove();

                    let index = selectElement.find('select')
                            .attr('id', (i, id) => id.replace(/(\d+)/, ($0, $1) => ++$1));

                    selectElement.append(options);

                    selectElement.val(ids[index]);
                });
            } else {
                selects.find('option').remove();
                selects.append(options);
            }
        },

        // ----------------------------------------

        loadManufacturerPopup: function(isNew) {
            const self = this;
            let manufacturerId = null;
            if (!isNew) {
                manufacturerId = $('#manufacturer_id').val();
                if (manufacturerId === '') {
                    return;
                }
                self.currentManufacturerTitle = $('#manufacturer_id option:selected').text();
            }

            new Ajax.Request(this.urlGetManufacturerPopupHtml, {
                method: 'post',
                parameters: {
                    manufacturer_id: manufacturerId,
                    account_id: self.getAccountId(),
                },
                onSuccess: function(transport) {
                    self.openManufacturerPopup(transport.responseText, isNew);
                },
            });
        },

        openManufacturerPopup: function(content, isNew) {
            const self = this;

            const modalDialogMessage = new Element('div', {
                id: 'modal_manufacturer_popup',
            });

            const messages = Object.create(MessagesObj);
            messages.setContainer('#modal_manufacturer_popup');

            const popup = $(modalDialogMessage).modal({
                title: $t('Manufacturer Details'),
                modalClass: 'width-550',
                buttons: [
                    {
                        text: $t('Cancel'),
                        class: 'action-secondary action-dismiss',
                        click: function() {
                            popup.modal('closeModal');
                            modalDialogMessage.remove();
                        },
                    }, {
                        text: $t('Save'),
                        class: 'action-primary action-accept',
                        id: 'save_popup_button',
                        click: function() {
                            if (!self.submitForm('manufacturer', self.urlManufactureUpdate, messages)) {
                                return false;
                            }

                            const newTitle = $('#name').val().trim();

                            if (isNew || newTitle !== self.currentManufacturerTitle) {
                                self.updateManufacturers(false);
                            }

                            popup.modal('closeModal');
                            modalDialogMessage.remove();
                        },
                    }],
            });

            popup.modal('openModal');
            modalDialogMessage.insert(content);
            modalDialogMessage.innerHTML.evalScripts();
        },

        loadResponsiblePersonPopup: function(isNew, index) {
            const self = this;
            let personId = null;
            if (!isNew) {
                let person = $(`#responsible_person_id_${index}`);

                personId = person.val();
                self.currentResponsiblePersonTitle = person.find('option:selected').text();

                if (personId === '') {
                    return;
                }
            }

            new Ajax.Request(this.urlGetResponsiblePersonPopupHtml, {
                method: 'post',
                parameters: {
                    responsible_person_id: personId,
                    account_id: self.getAccountId(),
                },
                onSuccess: function(transport) {
                    self.openResponsiblePersonPopup(transport.responseText, isNew);
                },
            });
        },

        openResponsiblePersonPopup: function(content, isNew) {
            const self = this;

            const modalDialogMessage = new Element('div', {
                id: 'modal_person_popup',
            });

            const messages = Object.create(MessagesObj);
            messages.setContainer('#modal_person_popup');

            const popup = $(modalDialogMessage).modal({
                title: $t('Responsible Person Details'),
                modalClass: 'width-550',
                buttons: [
                    {
                        text: $t('Cancel'),
                        class: 'action-secondary action-dismiss',
                        click: function() {
                            popup.modal('closeModal');
                            modalDialogMessage.remove();
                        },
                    }, {
                        text: $t('Save'),
                        class: 'action-primary action-accept',
                        id: 'save_popup_button',
                        click: function() {
                            const result = self.submitForm('responsible_person', self.urlGetResponsiblePersonUpdate, messages);
                            if (!result) {
                                return false;
                            }

                            const newName = $('#name').val().trim();
                            const newEmail = $('#email').val().trim();

                            const newTitle = `${newName} (${newEmail})`;

                            if (isNew || newTitle !== self.currentResponsiblePersonTitle) {
                                self.updateResponsiblePersons(false);
                            }

                            popup.modal('closeModal');
                            modalDialogMessage.remove();
                        },
                    }],
            });

            popup.modal('openModal');
            modalDialogMessage.insert(content);
            modalDialogMessage.innerHTML.evalScripts();
        },

        // ----------------------------------------

        addRow: function (event) {
            let clonedRow = $('.field-responsible_person_id').last().clone();

            this.incrementNameAndIdsInRow(clonedRow);
            clonedRow.find('.refresh_status ').closest('.action').remove();
            clonedRow.find('.remove_row').show();

            $(event.target).closest('.admin__field').before(clonedRow);

            this.initRemovePerson(clonedRow.find(this.removeRowButtonSelector));
            this.initResponsiblePersonLinks(clonedRow.find('.manufacturer_details'));
        },

        removeRow: function (event) {
            $(event.target).closest('.admin__field.field').remove();
        },

        // ----------------------------------------

        incrementNameAndIdsInRow: function (row) {
            $(row).find('select, input')
                    .attr('name', (i, name) => name.replace(/(\d+)/, ($0, $1) => ++$1))
                    .attr('id', (i, id) => id.replace(/(\d+)/, ($0, $1) => ++$1));

            $(row).find('label')
                    .attr('for', (i, id) => id.replace(/(\d+)/, ($0, $1) => ++$1));

            $(row).find('a')
                    .attr('data-index', (i, id) => id.replace(/(\d+)/, ($0, $1) => ++$1));
        },

        // ----------------------------------------

        submitForm: function(formId, url, messageObj) {
            const form = $('#' + formId);
            if (!form.valid()) {
                return false;
            }

            const formData = form.serialize(true);

            let result = false;
            new Ajax.Request(url, {
                method: 'post',
                asynchronous: false,
                parameters: formData,
                onSuccess: function(transport) {
                    const response = JSON.parse(transport.responseText);

                    if (response.result) {
                        result = true;

                        return;
                    }

                    messageObj.clear();
                    response.messages.each(function(message) {
                        messageObj.addError(message);
                    });
                },
            });

            return result;
        },
    });
});
