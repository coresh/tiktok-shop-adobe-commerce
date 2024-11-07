<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product;

class VariationFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Variation
    {
        return $this->objectManager->create(Variation::class);
    }
}
