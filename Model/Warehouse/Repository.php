<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Warehouse;

use M2E\TikTokShop\Model\ResourceModel\Warehouse as WarehouseResource;

class Repository
{
    use \M2E\TikTokShop\Model\CacheTrait;

    private WarehouseResource\CollectionFactory $collectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Warehouse $resource;
    private \M2E\TikTokShop\Model\WarehouseFactory $warehouseFactory;
    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Warehouse\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Warehouse $resource,
        \M2E\TikTokShop\Model\WarehouseFactory $warehouseFactory,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->warehouseFactory = $warehouseFactory;
        $this->cache = $cache;
    }

    public function get(int $id): \M2E\TikTokShop\Model\Warehouse
    {
        $warehouse = $this->find($id);
        if ($warehouse === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic("Warehouse '$id' not found.");
        }

        return $warehouse;
    }

    public function getByWarehouseId(string $warehouseId): \M2E\TikTokShop\Model\Warehouse
    {
        $warehouse = $this->findByWarehouseId($warehouseId);
        if ($warehouse === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                "Warehouse with warehouse id '$warehouseId' not found."
            );
        }

        return $warehouse;
    }

    public function find(int $id): ?\M2E\TikTokShop\Model\Warehouse
    {
        $warehouse = $this->warehouseFactory->create();

        $cacheData = $this->cache->getValue($this->makeCacheKey($warehouse, $id));
        if (!empty($cacheData)) {
            $this->initializeFromCache($warehouse, $cacheData);

            return $warehouse;
        }

        $this->resource->load($warehouse, $id);

        if ($warehouse->isObjectNew()) {
            return null;
        }

        $this->cache->setValue(
            $this->makeCacheKey($warehouse, $id),
            $this->getCacheDate($warehouse),
            [],
            60 * 60
        );

        return $warehouse;
    }

    public function findByWarehouseId(string $warehouseId): ?\M2E\TikTokShop\Model\Warehouse
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(WarehouseResource::COLUMN_WAREHOUSE_ID, $warehouseId);

        $entity = $collection->getFirstItem();

        return $entity->isObjectNew() ? null : $entity;
    }

    public function create(\M2E\TikTokShop\Model\Warehouse $warehouse): void
    {
        $this->resource->save($warehouse);
    }

    public function save(\M2E\TikTokShop\Model\Warehouse $warehouse): void
    {
        $this->resource->save($warehouse);
        $this->cache->removeValue($this->makeCacheKey($warehouse, $warehouse->getId()));
    }

    public function removeByShopId(int $shopId): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            [WarehouseResource::COLUMN_SHOP_ID . ' =?' => $shopId]
        );
    }

    /**
     * @param int $shopId
     *
     * @return \M2E\TikTokShop\Model\Warehouse[]
     */
    public function findByShop(int $shopId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(WarehouseResource::COLUMN_SHOP_ID, $shopId);

        return array_values($collection->getItems());
    }
}
