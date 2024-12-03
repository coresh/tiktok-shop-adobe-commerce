<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync;

class ProductBuilderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop
    ): \M2E\TikTokShop\Model\Listing\InventorySync\ProductBuilder {
        return $this->objectManager->create(
            \M2E\TikTokShop\Model\Listing\InventorySync\ProductBuilder::class,
            [
                'account' => $account,
                'shop' => $shop,
            ],
        );
    }
}
