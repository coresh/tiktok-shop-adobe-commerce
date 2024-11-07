<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

class StopQueueFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): StopQueue
    {
        return $this->objectManager->create(StopQueue::class);
    }
}
