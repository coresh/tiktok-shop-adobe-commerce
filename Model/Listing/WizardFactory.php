<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing;

class WizardFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Wizard
    {
        return $this->objectManager->create(Wizard::class);
    }
}
