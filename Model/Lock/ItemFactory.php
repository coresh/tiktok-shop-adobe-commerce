<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Lock;

class ItemFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Item
    {
        return $this->objectManager->create(Item::class);
    }

    public function create(string $nick, ?int $parentId): Item
    {
        $object = $this->createEmpty();
        $object->create($nick, $parentId);

        return $object;
    }
}
