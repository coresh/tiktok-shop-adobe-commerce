<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Observer\Product\AddUpdate;

class BeforeFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Before
    {
        return $this->objectManager->create(Before::class);
    }
}
