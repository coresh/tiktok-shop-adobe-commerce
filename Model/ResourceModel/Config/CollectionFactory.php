<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Config;

class CollectionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Collection
    {
        /** @var Collection */
        return $this->objectManager->create(Collection::class);
    }
}
