<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Item;

class BundleSkuFinderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\TikTokShop\Model\Magento\Product $magentoProduct,
        CombinedListingSkus $combinedListingSkus
    ): BundleSkuFinder {
        return $this->objectManager->create(BundleSkuFinder::class, [
            'magentoProduct' => $magentoProduct,
            'combinedListingSkus' => $combinedListingSkus,
        ]);
    }
}
