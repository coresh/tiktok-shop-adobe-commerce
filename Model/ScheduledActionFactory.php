<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

class ScheduledActionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): ScheduledAction
    {
        return $this->objectManager->create(ScheduledAction::class);
    }
}
