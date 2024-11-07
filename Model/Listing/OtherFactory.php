<?php

namespace M2E\TikTokShop\Model\Listing;

class OtherFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Other
    {
        return $this->objectManager->create(Other::class);
    }
}
