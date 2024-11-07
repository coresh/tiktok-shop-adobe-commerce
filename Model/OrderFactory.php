<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

class OrderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Order
    {
        return $this->objectManager->create(Order::class);
    }
}
