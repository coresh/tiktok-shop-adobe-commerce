<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Observer\StockItem\Save;

class AfterFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): After
    {
        return $this->objectManager->create(After::class);
    }
}
