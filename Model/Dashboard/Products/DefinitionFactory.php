<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Dashboard\Products;

class DefinitionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Definition
    {
        return $this->objectManager->create(Definition::class);
    }
}
