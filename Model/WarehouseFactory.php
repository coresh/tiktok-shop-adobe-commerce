<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

class WarehouseFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Warehouse
    {
        return $this->objectManager->create(Warehouse::class);
    }
}
