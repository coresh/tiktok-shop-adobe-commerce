<?php

namespace M2E\TikTokShop\Model\Magento\Product;

class ChangeAttributeTrackerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\TikTokShop\Model\Product $listingProduct,
        \M2E\TikTokShop\Model\Template\Description $templateDescription
    ): ChangeAttributeTracker {
        return $this->objectManager->create(ChangeAttributeTracker::class, [
            'listingProduct' => $listingProduct,
            'templateDescription' => $templateDescription,
        ]);
    }
}
