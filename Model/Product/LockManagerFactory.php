<?php

namespace M2E\TikTokShop\Model\Product;

class LockManagerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): \M2E\TikTokShop\Model\Product\LockManager
    {
        return $this->objectManager->create(\M2E\TikTokShop\Model\Product\LockManager::class);
    }
}
