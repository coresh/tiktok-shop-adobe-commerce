<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Item;

class CombinedListingSkus
{
    private array $combinedListingSkus;

    /**
     * @param array<\M2E\TikTokShop\Model\Order\Item\CombinedListingSku> $combinedListingSkus
     */
    public function __construct(array $combinedListingSkus)
    {
        $this->combinedListingSkus = $combinedListingSkus;
    }

    public function toArray(): array
    {
        return array_map(function ($sku) {
            return [
                \M2E\TikTokShop\Model\Order\Item\CombinedListingSku::KEY_SKU_ID => $sku->skuId,
                \M2E\TikTokShop\Model\Order\Item\CombinedListingSku::KEY_SKU_COUNT => $sku->skuCount,
                \M2E\TikTokShop\Model\Order\Item\CombinedListingSku::KEY_PRODUCT_ID => $sku->productId,
                \M2E\TikTokShop\Model\Order\Item\CombinedListingSku::KEY_SELLER_SKU => $sku->sellerSku,
            ];
        }, $this->combinedListingSkus);
    }

    /**
     * @return array<\M2E\TikTokShop\Model\Order\Item\CombinedListingSku>
     */
    public function getList(): array
    {
        return $this->combinedListingSkus;
    }
}
