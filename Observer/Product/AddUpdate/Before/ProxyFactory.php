<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Observer\Product\AddUpdate\Before;

class ProxyFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Proxy
    {
        return $this->objectManager->create(Proxy::class);
    }
}
