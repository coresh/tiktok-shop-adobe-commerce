<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Other;

use M2E\TikTokShop\Model\ResourceModel\Listing\Other as ListingOtherResource;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Listing\Other\CollectionFactory $collectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Listing\Other $resource;
    private \M2E\TikTokShop\Model\Listing\OtherFactory $objectFactory;
    private \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper,
        \M2E\TikTokShop\Model\ResourceModel\Listing\Other\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Listing\Other $resource,
        \M2E\TikTokShop\Model\Listing\OtherFactory $objectFactory
    ) {
        $this->dbStructureHelper = $dbStructureHelper;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->objectFactory = $objectFactory;
    }

    public function createCollection(): \M2E\TikTokShop\Model\ResourceModel\Listing\Other\Collection
    {
        return $this->collectionFactory->create();
    }

    public function create(\M2E\TikTokShop\Model\Listing\Other $other): void
    {
        $this->resource->save($other);
    }

    public function save(\M2E\TikTokShop\Model\Listing\Other $listingOther): void
    {
        $this->resource->save($listingOther);
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     */
    public function get(int $id): \M2E\TikTokShop\Model\Listing\Other
    {
        $obj = $this->objectFactory->create();
        $this->resource->load($obj, $id);

        if ($obj->isObjectNew()) {
            throw new \M2E\TikTokShop\Model\Exception("Object by id $id not found.");
        }

        return $obj;
    }

    public function remove(\M2E\TikTokShop\Model\Listing\Other $other): void
    {
        $this->resource->delete($other);
    }

    /**
     * @return \M2E\TikTokShop\Model\Listing\Other[]
     */
    public function findByIds(array $ids): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            ListingOtherResource::COLUMN_ID,
            ['in' => $ids],
        );

        return array_values($collection->getItems());
    }

    /**
     * @param int $id
     *
     * @return \M2E\TikTokShop\Model\Listing\Other|null
     */
    public function findById(int $id): ?\M2E\TikTokShop\Model\Listing\Other
    {
        $obj = $this->objectFactory->create();
        $this->resource->load($obj, $id);

        if ($obj->isObjectNew()) {
            return null;
        }

        return $obj;
    }

    /**
     * @param array $productsIds
     *
     * @return \M2E\TikTokShop\Model\Listing\Other[]
     */
    public function findByProductIds(array $productsIds, int $accountId, int $shopId): array
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                ListingOtherResource::COLUMN_TTS_PRODUCT_ID,
                ['in' => $productsIds],
            )
            ->addFieldToFilter(ListingOtherResource::COLUMN_ACCOUNT_ID, $accountId)
            ->addFieldToFilter(ListingOtherResource::COLUMN_SHOP_ID, $shopId);

        return array_values($collection->getItems());
    }

    public function removeByAccountId(int $accountId): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            ['account_id = ?' => $accountId],
        );
    }

    public function findByMagentoProductId(int $magentoProductId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(\M2E\TikTokShop\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return array_values($collection->getItems());
    }

    public function findRemovedMagentoProductIds(): array
    {
        $collection = $this->collectionFactory->create();

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns(
            ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID
        );
        $collection->addFieldToFilter(ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID, ['notnull' => true]);
        $collection->getSelect()->distinct();

        $entityTableName = $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity');

        $collection->getSelect()->joinLeft(
            ['cpe' => $entityTableName],
            sprintf(
                'cpe.entity_id = `main_table`.%s',
                ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID
            ),
            []
        );
        $collection->getSelect()->where('cpe.entity_id IS NULL');

        $result = [];
        foreach ($collection->toArray()['items'] ?? [] as $row) {
            $result[] = (int)$row[ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID];
        }

        return $result;
    }
}
