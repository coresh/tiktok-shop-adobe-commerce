<?php

namespace M2E\TikTokShop\Model\Magento\Product;

class StockItemFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(array $data = []): StockItem
    {
        return $this->objectManager->create(StockItem::class, $data);
    }
}
