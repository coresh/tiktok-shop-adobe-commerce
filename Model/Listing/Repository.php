<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing;

class Repository
{
    use \M2E\TikTokShop\Model\CacheTrait;

    private \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Listing $listingResource;
    private \M2E\TikTokShop\Model\ListingFactory $listingFactory;
    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Listing $listingResource,
        \M2E\TikTokShop\Model\ListingFactory $listingFactory,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache
    ) {
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->listingResource = $listingResource;
        $this->listingFactory = $listingFactory;
        $this->cache = $cache;
    }

    public function getListingsCount(): int
    {
        $collection = $this->listingCollectionFactory->create();

        return $collection->getSize();
    }

    public function get(int $id): \M2E\TikTokShop\Model\Listing
    {
        $listing = $this->find($id);
        if ($listing === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Listing does not exist.');
        }

        return $listing;
    }

    public function find(int $id): ?\M2E\TikTokShop\Model\Listing
    {
        $listing = $this->listingFactory->create();

        $cacheData = $this->cache->getValue($this->makeCacheKey($listing, $id));
        if (!empty($cacheData)) {
            $this->initializeFromCache($listing, $cacheData);

            return $listing;
        }

        $this->listingResource->load($listing, $id);

        if ($listing->isObjectNew()) {
            return null;
        }

        $this->cache->setValue(
            $this->makeCacheKey($listing, $id),
            $this->getCacheDate($listing),
            [],
            60 * 60
        );

        return $listing;
    }

    public function save(\M2E\TikTokShop\Model\Listing $listing)
    {
        $this->listingResource->save($listing);
        $this->cache->removeValue($this->makeCacheKey($listing, $listing->getId()));
    }

    public function remove(\M2E\TikTokShop\Model\Listing $listing)
    {
        $this->listingResource->delete($listing);
        $this->cache->removeValue($this->makeCacheKey($listing, $listing->getId()));
    }
}
