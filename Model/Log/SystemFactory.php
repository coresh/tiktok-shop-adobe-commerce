<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Log;

class SystemFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): System
    {
        return $this->objectManager->create(System::class);
    }
}
