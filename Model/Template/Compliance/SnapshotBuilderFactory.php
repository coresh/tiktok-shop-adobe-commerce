<?php

namespace M2E\TikTokShop\Model\Template\Compliance;

use M2E\TikTokShop\Model\Template\Compliance\SnapshotBuilder;

class SnapshotBuilderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): SnapshotBuilder
    {
        return $this->objectManager->create(SnapshotBuilder::class);
    }
}
