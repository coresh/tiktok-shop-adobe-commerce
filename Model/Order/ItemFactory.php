<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order;

class ItemFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(int $orderId, string $ttsItemId): Item
    {
        $item = $this->objectManager->create(Item::class);
        $item->create($orderId, $ttsItemId);

        return $item;
    }

    public function createEmpty(): Item
    {
        return $this->objectManager->create(Item::class);
    }
}
