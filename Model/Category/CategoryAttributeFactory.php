<?php

namespace M2E\TikTokShop\Model\Category;

class CategoryAttributeFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): \M2E\TikTokShop\Model\Category\CategoryAttribute
    {
        return $this->objectManager->create(\M2E\TikTokShop\Model\Category\CategoryAttribute::class);
    }
}
