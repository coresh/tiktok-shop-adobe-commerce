<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Shop;

class Repository
{
    use \M2E\TikTokShop\Model\CacheTrait;

    private \M2E\TikTokShop\Model\ShopFactory $entityFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Shop\CollectionFactory $collectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Shop $resource;
    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\TikTokShop\Model\ShopFactory $entityFactory,
        \M2E\TikTokShop\Model\ResourceModel\Shop\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Shop $resource,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache
    ) {
        $this->entityFactory = $entityFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->cache = $cache;
    }

    public function find(int $id): ?\M2E\TikTokShop\Model\Shop
    {
        $shop = $this->entityFactory->create();

        $cachedData = $this->cache->getValue($this->makeCacheKey($shop, $id));
        if (!empty($cachedData)) {
            $this->initializeFromCache($shop, $cachedData);

            return $shop;
        }

        $this->resource->load($shop, $id);

        if ($shop->isObjectNew()) {
            return null;
        }

        $this->cache->setValue(
            $this->makeCacheKey($shop, $id),
            $this->getCacheDate($shop),
            [],
            60 * 60
        );

        return $shop;
    }

    public function get(int $shopId): \M2E\TikTokShop\Model\Shop
    {
        $shop = $this->find($shopId);
        if ($shop === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Shop not found.');
        }

        return $shop;
    }

    /**
     * @return \M2E\TikTokShop\Model\Shop[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }

    /**
     * @param int $accountId
     *
     * @return \M2E\TikTokShop\Model\Shop[]
     */
    public function findForAccount(int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('account_id', $accountId);

        return array_values($collection->getItems());
    }

    public function create(\M2E\TikTokShop\Model\Shop $shop): void
    {
        $this->resource->save($shop);
    }

    public function save(\M2E\TikTokShop\Model\Shop $shop): void
    {
        $this->resource->save($shop);
        $this->cache->removeValue($this->makeCacheKey($shop, $shop->getId()));
    }

    public function remove(\M2E\TikTokShop\Model\Shop $shop): void
    {
        $this->resource->delete($shop);
        $this->cache->removeValue($this->makeCacheKey($shop, $shop->getId()));
    }

    public function isExistInEuRegion(): bool
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            \M2E\TikTokShop\Model\ResourceModel\Shop::COLUMN_REGION,
            ['in' => \M2E\TikTokShop\Model\Shop\Region::EU_REGION_CODES]
        );

        return (int)$collection->getSize() > 0;
    }
}
