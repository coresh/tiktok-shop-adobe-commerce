<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing;

class PartialDataFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): PartialData
    {
        return $this->objectManager->create(PartialData::class);
    }
}
