<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ScheduledAction;

use M2E\TikTokShop\Model\ResourceModel\ScheduledAction as ScheduledActionResource;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\ScheduledAction $resource;
    private ScheduledActionResource\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\ScheduledAction                           $resource,
        ScheduledActionResource\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
    }

    public function create(\M2E\TikTokShop\Model\ScheduledAction $action): void
    {
        $this->resource->save($action);
    }

    /**
     * @param \M2E\TikTokShop\Model\ScheduledAction[] $ids
     *
     * @return array
     */
    public function getByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $collection  = $this->collectionFactory->create();
        $collection->addFieldToFilter(ScheduledActionResource::COLUMN_ID, array_unique($ids));

        return array_values($collection->getItems());
    }

    public function findByListingProductId(int $listingProductId): ?\M2E\TikTokShop\Model\ScheduledAction
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID, $listingProductId);

        /** @var \M2E\TikTokShop\Model\ScheduledAction $item */
        $item = $collection->getFirstItem();
        if ($item->isObjectNew()) {
            return null;
        }

        return $item;
    }

    public function remove(\M2E\TikTokShop\Model\ScheduledAction $action): void
    {
        $this->resource->delete($action);
    }
}
