<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Item;

class CombinedListingSku
{
    public const KEY_SKU_ID = 'sku_id';
    public const KEY_SKU_COUNT = 'sku_count';
    public const KEY_PRODUCT_ID = 'product_id';
    public const KEY_SELLER_SKU = 'seller_sku';

    public string $skuId;
    public int $skuCount;
    public string $productId;
    public string $sellerSku;

    public function __construct(
        string $skuId,
        int $skuCount,
        string $productId,
        string $sellerSku
    ) {
        $this->skuId = $skuId;
        $this->skuCount = $skuCount;
        $this->productId = $productId;
        $this->sellerSku = $sellerSku;
    }
}
