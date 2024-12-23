define([
    'uiElement',
    'mage/translate',
    'TikTokShop/Category/Chooser'
], (uiElement, $t) => {
    'use strict';

    return uiElement.extend({
        queryMinChars: 3,

        defaults: {
            query: '',
            foundCategories: [],
            searched: false,
            hasMoreCategories: false,
            hasFoundCategories: false,
            searchUrl: '',
            shopId: 0,
            chooserManager: window.TikTokShopCategoryChooserObj,
            tracks: {
                'foundCategories': true,
                'query': true,
                'hasMoreCategories': true,
                'searched': true,
                'hasFoundCategories': true
            },
        },

        // ----------------------------------------

        reset() {
            this.query = '';
            this.foundCategories = [];
            this.searched = false;
            this.hasFoundCategories = false;
            this.hasMoreCategories = false;
            this.chooserManager.messagesClearOnCategoryChangeBlock();
        },

        search() {
            if (this.query.length < this.queryMinChars) {
                this.chooserManager.messageAddErrorToCategoryChangeBlock(
                        $t('The search query is too short. Please enter at least 3 characters.')
                );

                return;
            }

            this.chooserManager.messagesClearOnCategoryChangeBlock();

            this.doSearch(this.query);
        },

        searchOnEnter(uiElement, event) {
            if (event.which !== 13) {
                return true;
            }

            this.search();
        },

        selectCategory(categoryId) {
            this.chooserManager.selectCategory(categoryId);
        },

        // ----------------------------------------

        doSearch(query) {
            new Ajax.Request(this.searchUrl, {
                method: 'post',
                asynchronous: true,
                parameters: {
                    'search_query': query,
                    'shop_id': this.shopId,
                },
                onSuccess: this.processCategories.bind(this),
            });
        },

        processCategories(transport) {
            this.foundCategories = [];

            const response = transport.responseText.evalJSON();

            response.categories.forEach(
                    (categoryData) => {
                        let name = `${categoryData.path} (${categoryData.id})`;
                        if (categoryData.is_invite) {
                            name += ` [${$t('INVITE ONLY')}]`;
                        }

                        if (!categoryData.is_valid) {
                            name += ` [${$t('INVALID CATEGORY')}]`;
                        }

                        this.foundCategories.push(
                                {
                                    'id': categoryData.id,
                                    'name': name,
                                    'is_invite_only': categoryData.is_invite,
                                    'is_valid': categoryData.is_valid,
                                },
                        );
                    },
            );
            this.hasMoreCategories = response.has_more;
            this.hasFoundCategories = this.foundCategories.length > 0;
            this.searched = true;
        },
    });
});
