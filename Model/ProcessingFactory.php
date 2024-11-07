<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

class ProcessingFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Processing
    {
        return $this->objectManager->create(Processing::class);
    }
}
