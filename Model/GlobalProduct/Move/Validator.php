<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\GlobalProduct\Move;

class Validator
{
    public function isDifferentAccount(
        \M2E\TikTokShop\Model\Listing $sourceListing,
        \M2E\TikTokShop\Model\Listing $targetListing
    ): bool {
        return $sourceListing->getAccountId() !== $targetListing->getAccountId();
    }

    public function isProductListed(\M2E\TikTokShop\Model\Product $product): bool
    {
        if ($product->isGlobalProduct()) {
            return true;
        }

        return $product->isStatusListed();
    }

    public function isSameShop(
        \M2E\TikTokShop\Model\Listing $sourceListing,
        \M2E\TikTokShop\Model\Listing $targetListing
    ): bool {
        return $sourceListing->getShopId() === $targetListing->getShopId();
    }
}
