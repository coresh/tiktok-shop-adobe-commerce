<?php

namespace M2E\TikTokShop\Model\Template;

class DescriptionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Description
    {
        return $this->objectManager->create(Description::class);
    }
}
