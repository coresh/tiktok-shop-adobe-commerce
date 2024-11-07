<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Magento\Product;

use M2E\TikTokShop\Model\ResourceModel\MSI\Magento\Product\Collection as MSICollection;

class CollectionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private \M2E\TikTokShop\Helper\Magento $magentoHelper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\TikTokShop\Helper\Magento $magentoHelper
    ) {
        $this->objectManager = $objectManager;
        $this->magentoHelper = $magentoHelper;
    }

    /**
     * @param array $data
     *
     * @return \M2E\TikTokShop\Model\ResourceModel\Magento\Product\Collection
     */
    public function create(array $data = []): Collection
    {
        return $this->magentoHelper->isMSISupportingVersion()
            ? $this->objectManager->create(MSICollection::class, $data)
            : $this->objectManager->create(Collection::class, $data);
    }
}
