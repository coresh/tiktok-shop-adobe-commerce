<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

class ManufacturerConfigurationFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): ManufacturerConfiguration
    {
        return $this->objectManager->create(ManufacturerConfiguration::class);
    }
}
