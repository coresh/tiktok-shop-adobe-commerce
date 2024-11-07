<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Warehouse;

class ShippingMappingFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(array $shippingMappings): ShippingMapping
    {
        return $this->objectManager->create(ShippingMapping::class, [
            'shippingMappings' => $shippingMappings
        ]);
    }
}
