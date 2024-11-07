<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Template\Synchronization;

use M2E\TikTokShop\Model\ResourceModel\Template\Synchronization\Collection as SynchronizationCollection;

class CollectionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): SynchronizationCollection
    {
        return $this->objectManager->create(SynchronizationCollection::class);
    }
}
