<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Attribute;

class ValidateMagentoProductFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createWithCategory(\M2E\TikTokShop\Model\Category\Dictionary $categoryDictionary): ValidateMagentoProduct
    {
        return $this->objectManager->create(ValidateMagentoProduct::class, [
            'categoryDictionary' => $categoryDictionary,
        ]);
    }
}
