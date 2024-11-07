<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Dictionary;

class DeleteService
{
    private \Magento\Framework\App\ResourceConnection $resource;
    private \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary\CollectionFactory $dictionaryCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Category\Attribute $categoryAttributeResource;
    private \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary $categoryDictionaryResource;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary\CollectionFactory $dictionaryCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Category\Attribute $categoryAttributeResource,
        \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary $categoryDictionaryResource
    ) {
        $this->resource = $resource;
        $this->dictionaryCollectionFactory = $dictionaryCollectionFactory;
        $this->categoryAttributeResource = $categoryAttributeResource;
        $this->categoryDictionaryResource = $categoryDictionaryResource;
    }

    public function deleteByShop(\M2E\TikTokShop\Model\Shop $shop)
    {
        $connection = $this->resource->getConnection();
        $transaction = $connection->beginTransaction();

        try {
            $this->removeRelatedAttributesByShopId($shop);
            $connection = $this->resource->getConnection();
            $connection->delete(
                $this->categoryDictionaryResource->getMainTable(),
                [\M2E\TikTokShop\Model\ResourceModel\Category\Dictionary::COLUMN_SHOP_ID . ' = ?' => $shop->getId()]
            );
        } catch (\Throwable $exception) {
            $transaction->rollBack();
        }

        $transaction->commit();
    }

    private function removeRelatedAttributesByShopId(\M2E\TikTokShop\Model\Shop $shop)
    {
        $dictionaryCollection = $this->dictionaryCollectionFactory->create();
        $dictionaryCollection->addFieldToFilter(
            \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary::COLUMN_SHOP_ID,
            ['eq' => $shop->getId()]
        );

        $select = $dictionaryCollection->getSelect();

        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->columns(\M2E\TikTokShop\Model\ResourceModel\Category\Dictionary::COLUMN_ID);

        $connection = $this->resource->getConnection();
        $connection->delete(
            $this->categoryAttributeResource->getMainTable(),
            [
                sprintf(
                    '%s IN (?)',
                    \M2E\TikTokShop\Model\ResourceModel\Category\Attribute::COLUMN_CATEGORY_DICTIONARY_ID
                ) => $dictionaryCollection->getSelect(),
            ]
        );
    }
}
