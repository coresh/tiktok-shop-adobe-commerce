<?php

namespace M2E\TikTokShop\Model\ResourceModel\Tag;

class CollectionFactory
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return \M2E\TikTokShop\Model\ResourceModel\Tag\Collection
     */
    public function create(): Collection
    {
        return $this->objectManager->create(Collection::class);
    }
}
