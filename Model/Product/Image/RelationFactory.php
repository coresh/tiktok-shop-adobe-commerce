<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\Image;

class RelationFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Relation
    {
        return $this->objectManager->create(Relation::class);
    }
}
