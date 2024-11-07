<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product\Type;

class ConfigurableFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Configurable
    {
        return $this->objectManager->create(Configurable::class);
    }
}
