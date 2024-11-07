<?php

namespace M2E\TikTokShop\Model\ResourceModel\Image;

class CollectionFactory
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Collection
    {
        return $this->objectManager->create(Collection::class);
    }
}
