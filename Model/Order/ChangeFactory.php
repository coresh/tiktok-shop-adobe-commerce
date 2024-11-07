<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order;

class ChangeFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Change
    {
        return $this->objectManager->create(Change::class);
    }
}
