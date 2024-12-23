define([
    'mage/translate',
    'TikTokShop/Plugin/Messages',
], function($t, MessagesObj) {

    window.TikTokShopTemplateCompliance = Class.create({

        accounts: null,
        selectedAccountId: null,
        manufacturerId: null,
        responsiblePersonId: null,
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
            this.setResponsiblePersonId(config.responsiblePersonId);

            this.initAccount();
        },

        // ----------------------------------------

        initAccount: function() {
            const self = this;

            $('account_id').observe('change', function() {
                self.setAccountId($('account_id').value || self.selectedAccountId);
            });
        },

        hasAccountId: function() {
            return this.accountId !== null;
        },

        setAccountId: function(id) {
            this.accountId = parseInt(id) || null;

            if (this.hasAccountId()) {
                jQuery('#refresh_manufacturer, #refresh_responsible_person').show();
                jQuery('.actions').show();

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
            const select = jQuery('#manufacturer_id');
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
            return this.responsiblePersonId !== null;
        },

        setResponsiblePersonId: function(id) {
            this.responsiblePersonId = id || null;
        },

        getResponsiblePersonId: function() {
            return this.responsiblePersonId;
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
            const select = jQuery('#responsible_person_id');
            select.find('option').remove();

            persons.each(function(person) {
                select.append(new Option(person.title, person.id));
            });

            if (this.hasResponsiblePersonId()) {
                select.val(this.getResponsiblePersonId());
            }
        },

        // ----------------------------------------

        loadManufacturerPopup: function(isNew) {
            const self = this;
            let manufacturerId = null;
            if (!isNew) {
                manufacturerId = jQuery('#manufacturer_id').val();
                if (manufacturerId === '') {
                    return;
                }
                self.currentManufacturerTitle = jQuery('#manufacturer_id option:selected').text();
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

            const popup = jQuery(modalDialogMessage).modal({
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

                            const newTitle = jQuery('#name').val().trim();

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

        loadResponsiblePersonPopup: function(isNew) {
            const self = this;
            let personId = null;
            if (!isNew) {
                personId = jQuery('#responsible_person_id').val();
                self.currentResponsiblePersonTitle = jQuery('#responsible_person_id option:selected').text();
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

            const popup = jQuery(modalDialogMessage).modal({
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

                            const newName = jQuery('#name').val().trim();
                            const newEmail = jQuery('#email').val().trim();

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

        submitForm: function(formId, url, messageObj) {
            const form = jQuery('#' + formId);
            if (!form.validation() || !form.validation('isValid')) {
                return false;
            }

            const self = this;

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
