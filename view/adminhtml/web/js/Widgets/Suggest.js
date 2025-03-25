
define([
    'jquery',
    'mage/backend/suggest'
], function ($) {
    'use strict';

    $.widget('m2e.suggest', $.mage.suggest, {
        _create: function () {
            this._super();
        },

        /**
         * @override
         */
        _bind: function () {
            this._on($.extend({
                /**
                 * @param {jQuery.Event} event
                 */
                keydown: function (event) {
                    var keyCode = $.ui.keyCode;

                    switch (event.keyCode) {
                        case keyCode.PAGE_UP:
                        case keyCode.UP:
                            if (!event.shiftKey) {
                                event.preventDefault();
                                this._proxyEvents(event);
                            }
                            break;

                        case keyCode.PAGE_DOWN:
                        case keyCode.DOWN:
                            if (!event.shiftKey) {
                                event.preventDefault();
                                this._proxyEvents(event);
                            }
                            break;

                        case keyCode.TAB:
                            if (this.isDropdownShown()) {
                                this._onSelectItem(event, null);
                                event.preventDefault();
                            }
                            break;

                        case keyCode.ENTER:
                        case keyCode.NUMPAD_ENTER:
                            this._toggleEnter(event);

                            if (this.isDropdownShown() && this._focused) {
                                this._proxyEvents(event);
                                event.preventDefault();
                            }
                            break;

                        case keyCode.ESCAPE:
                            if (this.isDropdownShown()) {
                                event.stopPropagation();
                            }
                            this.close(event);
                            this._blurItem();
                            break;
                    }
                },

                /**
                 * @param {jQuery.Event} event
                 */
                keyup: function (event) {
                    var keyCode = $.ui.keyCode;

                    switch (event.keyCode) {
                        case keyCode.HOME:
                        case keyCode.END:
                        case keyCode.PAGE_UP:
                        case keyCode.PAGE_DOWN:
                        case keyCode.ESCAPE:
                        case keyCode.UP:
                        case keyCode.DOWN:
                        case keyCode.LEFT:
                        case keyCode.RIGHT:
                        case keyCode.TAB:
                            break;

                        case keyCode.ENTER:
                        case keyCode.NUMPAD_ENTER:
                            if (this.isDropdownShown()) {
                                event.preventDefault();
                            }
                            break;
                        default:
                            this.search(event);
                    }
                },

                /**
                 * @param {jQuery.Event} event
                 */
                blur: function (event) {
                    if (!this.preventBlur) {
                        this._abortSearch();
                        this.close(event);
                        this._change(event);
                    } else {
                        this.element.trigger('focus');
                    }
                },
                cut: this.search,
                paste: this.search,
                input: this.search,
                selectItem: this._onSelectItem,
                click: this.search
            }, this.options.events));

            this._bindSubmit();
            this._bindDropdown();
        },

        /**
         * @override
         */
        _focusItem: function (e, ui) {
            if (ui && ui.item) {
                this._focused = ui.item;

                $(e.currentTarget).find('li').removeClass('_active');
                this._focused.addClass('_active')

                this._trigger('focus', e, {
                    item: this._focused
                });
            }
        },

        /**
         * @override
         */
        _blurItem: function () {
            this._focused = null;
            this._trigger('blur');
        },

        /**
         * @override
         */
        _selectItem: function (e) {
            if (this._focused) {
                this._term = this._readItemData(this._focused).label;
                this.element.val(this._term);
                this.close(e);
            }
        },
    });


    return $.m2e.suggest;
});
