<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing;

class LockFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function create(): Lock
    {
        return $this->objectManager->create(Lock::class);
    }
}
