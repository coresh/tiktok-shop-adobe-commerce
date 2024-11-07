<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Template;

class ManagerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Manager
    {
        return $this->objectManager->create(Manager::class);
    }
}
