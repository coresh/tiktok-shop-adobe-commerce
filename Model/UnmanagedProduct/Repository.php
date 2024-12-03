<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\UnmanagedProduct;

use M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct as UnmanagedProductResource;
use M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku as VariantSkuResource;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\CollectionFactory $collectionUnmanagedFactory;
    private \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku\CollectionFactory $productVariantCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct $unmanagedResource;
    private \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku $variantResource;
    private \M2E\TikTokShop\Model\UnmanagedProductFactory $objectFactory;
    private \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku\CollectionFactory $productVariantCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct $unmanagedResource,
        \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku $variantResource,
        \M2E\TikTokShop\Model\UnmanagedProductFactory $unmanagedProductFactory,
        \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper
    ) {
        $this->collectionUnmanagedFactory = $collectionFactory;
        $this->unmanagedResource = $unmanagedResource;
        $this->variantResource = $variantResource;
        $this->objectFactory = $unmanagedProductFactory;
        $this->productVariantCollectionFactory = $productVariantCollectionFactory;
        $this->dbStructureHelper = $dbStructureHelper;
    }

    public function createCollection(): \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\Collection
    {
        return $this->collectionUnmanagedFactory->create();
    }

    public function create(\M2E\TikTokShop\Model\UnmanagedProduct $unmanaged): void
    {
        $this->unmanagedResource->save($unmanaged);
    }

    public function save(\M2E\TikTokShop\Model\UnmanagedProduct $unmanaged): void
    {
        $this->unmanagedResource->save($unmanaged);
    }

    /**
     * @param \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku[] $variantsSku
     */
    public function saveVariants(array $variantsSku): void
    {
        foreach ($variantsSku as $variantSku) {
            $this->saveVariant($variantSku);
        }
    }

    public function saveVariant(\M2E\TikTokShop\Model\UnmanagedProduct\VariantSku $variant): void
    {
        $this->variantResource->save($variant);
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     */
    public function get(int $id): \M2E\TikTokShop\Model\UnmanagedProduct
    {
        $obj = $this->objectFactory->create();
        $this->unmanagedResource->load($obj, $id);

        if ($obj->isObjectNew()) {
            throw new \M2E\TikTokShop\Model\Exception("Object by id $id not found.");
        }

        return $obj;
    }

    public function deleteVariant(\M2E\TikTokShop\Model\UnmanagedProduct\VariantSku $variantSku): void
    {
        $this->variantResource->delete($variantSku);
    }

    public function delete(\M2E\TikTokShop\Model\UnmanagedProduct $listingProduct): void
    {
        $this->unmanagedResource->delete($listingProduct);
    }

    /**
     * @return \M2E\TikTokShop\Model\UnmanagedProduct[]
     */
    public function findByIds(array $ids): array
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $collection->addFieldToFilter(
            UnmanagedProductResource::COLUMN_ID,
            ['in' => $ids],
        );

        return array_values($collection->getItems());
    }

    /**
     * @param int $id
     *
     * @return \M2E\TikTokShop\Model\UnmanagedProduct|null
     */
    public function findById(int $id): ?\M2E\TikTokShop\Model\UnmanagedProduct
    {
        $obj = $this->objectFactory->create();
        $this->unmanagedResource->load($obj, $id);

        if ($obj->isObjectNew()) {
            return null;
        }

        return $obj;
    }

    /**
     * @param array $productsIds
     *
     * @return \M2E\TikTokShop\Model\UnmanagedProduct[]
     */
    public function findByProductIds(array $productsIds, int $accountId, int $shopId): array
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $collection
            ->addFieldToFilter(
                UnmanagedProductResource::COLUMN_TTS_PRODUCT_ID,
                ['in' => $productsIds],
            )
            ->addFieldToFilter(UnmanagedProductResource::COLUMN_ACCOUNT_ID, $accountId)
            ->addFieldToFilter(UnmanagedProductResource::COLUMN_SHOP_ID, $shopId);

        return array_values($collection->getItems());
    }

    /**
     * @param int $accountId
     *
     * @return void
     */
    public function removeProductByAccountId(int $accountId): void
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            ['account_id = ?' => $accountId],
        );
    }

    /**
     * @param int $accountId
     *
     * @return void
     */
    public function removeVariantsByAccountId(int $accountId): void
    {
        $collection = $this->productVariantCollectionFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            ['account_id = ?' => $accountId],
        );
    }

    /**
     * @param int $magentoProductId
     *
     * @return \M2E\TikTokShop\Model\UnmanagedProduct[]
     */
    public function findProductByMagentoProductId(int $magentoProductId): array
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $collection->addFieldToFilter(UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return array_values($collection->getItems());
    }

    /**
     * @param \M2E\TikTokShop\Model\UnmanagedProduct $product
     *
     * @return \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku[]
     */
    public function findVariantsByProduct(\M2E\TikTokShop\Model\UnmanagedProduct $product): array
    {
        $collection = $this->productVariantCollectionFactory->create();
        $collection->addFieldToFilter(VariantSkuResource::COLUMN_PRODUCT_ID, $product->getId());

        return array_values($collection->getItems());
    }

    /**
     * @param int $id
     *
     * @return \M2E\TikTokShop\Model\UnmanagedProduct\VariantSku[]
     */
    public function findVariantsByMagentoProductId(int $id): array
    {
        $collection = $this->productVariantCollectionFactory->create();
        $collection->addFieldToFilter(VariantSkuResource::COLUMN_MAGENTO_PRODUCT_ID, $id);

        return array_values($collection->getItems());
    }

    public function findRemovedMagentoProductIds(): array
    {
        $collection = $this->createCollection();

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns(
            UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID
        );
        $collection->addFieldToFilter(UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID, ['notnull' => true]);
        $collection->getSelect()->distinct();

        $entityTableName = $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity');

        $collection->getSelect()->joinLeft(
            ['cpe' => $entityTableName],
            sprintf(
                'cpe.entity_id = `main_table`.%s',
                UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID
            ),
            []
        );
        $collection->getSelect()->where('cpe.entity_id IS NULL');

        $result = [];
        foreach ($collection->toArray()['items'] ?? [] as $row) {
            $result[] = (int)$row[UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID];
        }

        return $result;
    }

    public function isExistForAccountId(int $accountId): bool
    {
        $collection = $this->collectionUnmanagedFactory->create();
        $collection->addFieldToFilter(UnmanagedProductResource::COLUMN_ACCOUNT_ID, $accountId);

        return (int)$collection->getSize() > 0;
    }
}
