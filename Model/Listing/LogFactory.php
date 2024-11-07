<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing;

class LogFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Log
    {
        return $this->objectManager->create(Log::class);
    }
}
