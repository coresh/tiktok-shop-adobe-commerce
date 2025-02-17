define([
    'TikTokShop/Product/Unmanaged/Move/RetrieveSelected',
    'TikTokShop/Product/Unmanaged/Move/PrepareProducts',
    'TikTokShop/Product/Unmanaged/Move/Processor',
], (RetrieveSelected, PrepareProducts, MoveProcess) => {
    'use strict';

    return {
        startMoveForProduct: (id, urlPrepareMove, urlGrid, urlListingCreate, accountId) => {
            PrepareProducts.prepareProducts(
                    urlPrepareMove,
                    [id],
                    accountId,
                    function (shopId) {
                        MoveProcess.openMoveToListingGrid(
                                urlGrid,
                                urlListingCreate,
                                accountId,
                                shopId
                        );
                    }
            );
        },

        startMoveForProducts: (massActionData, urlPrepareMove, urlGrid, urlGetSelectedProducts, urlListingCreate, accountId) => {
            RetrieveSelected.getSelectedProductIds(
                    massActionData,
                    urlGetSelectedProducts,
                    accountId,
                    function (selectedProductIds) {
                        PrepareProducts.prepareProducts(
                                urlPrepareMove,
                                selectedProductIds,
                                accountId,
                                function (shopId) {
                                    MoveProcess.openMoveToListingGrid(
                                            urlGrid,
                                            urlListingCreate,
                                            accountId,
                                            shopId
                                    );
                                }
                        );
                    }
            );
        }
    };
});
