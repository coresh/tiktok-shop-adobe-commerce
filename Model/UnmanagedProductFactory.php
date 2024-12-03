<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

class UnmanagedProductFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): UnmanagedProduct
    {
        return $this->objectManager->create(UnmanagedProduct::class);
    }
}
