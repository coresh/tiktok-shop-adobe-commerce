require([
    'jquery',
    'TikTokShop/Product/Unmanaged/Move',
    'TikTokShop/Product/Unmanaged/Link'
], ($, Move, Link) => {
    'use strict';

    $(document).on('click', '[id^=row_link_]', function (event) {
        const el = $(event.currentTarget);
        const otherProductId = el.data('id');
        const productTitle = el.data('title');
        const url = el.data('url');

        Link.openSelectProductPopUp(otherProductId, productTitle, url);
    }.bind(this));

    $(document).on('click', '[id^=row_move_]', function (event) {
        const el = $(event.currentTarget);
        const otherProductId = el.data('id');
        const urlPrepareMove = el.data('url_move');
        const urlGrid = el.data('url_grid');
        const urlListingCreate = el.data('url_new_listing');
        let accountId = $('#account_switcher').val();

        Move.startMoveForProduct(otherProductId, urlPrepareMove, urlGrid, urlListingCreate, accountId);
    }.bind(this));
});
