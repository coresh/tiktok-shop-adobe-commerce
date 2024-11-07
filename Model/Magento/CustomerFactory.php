<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento;

class CustomerFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Customer
    {
        return $this->objectManager->create(Customer::class);
    }
}
