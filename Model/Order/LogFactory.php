<?php

namespace M2E\TikTokShop\Model\Order;

class LogFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(array $data = []): Log
    {
        return $this->objectManager->create(Log::class, $data);
    }
}
