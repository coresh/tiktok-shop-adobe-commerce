<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing;

class LockManagerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\TikTokShop\Model\Processing $processing): LockManager
    {
        return $this->objectManager->create(LockManager::class, ['processing' => $processing]);
    }
}
