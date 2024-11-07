<?php

namespace M2E\TikTokShop\Model\Category;

class DictionaryFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Dictionary
    {
        return $this->objectManager->create(Dictionary::class);
    }
}
