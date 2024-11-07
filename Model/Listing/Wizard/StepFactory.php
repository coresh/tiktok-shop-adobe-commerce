<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Wizard;

class StepFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Step
    {
        return $this->objectManager->create(Step::class);
    }
}
