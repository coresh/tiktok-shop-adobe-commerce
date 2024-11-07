<?php

namespace M2E\TikTokShop\Model;

class ShippingProviderFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): ShippingProvider
    {
        return $this->objectManager->create(ShippingProvider::class);
    }
}
