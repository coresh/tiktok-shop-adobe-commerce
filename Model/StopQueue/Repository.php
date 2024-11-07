<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\StopQueue;

use M2E\TikTokShop\Model\ResourceModel\StopQueue as ResourceModel;

class Repository
{
    private ResourceModel\CollectionFactory $collectionFactory;
    /** @var \M2E\TikTokShop\Model\ResourceModel\StopQueue */
    private ResourceModel $stopQueueResource;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\StopQueue                                   $stopQueueResource,
        ResourceModel\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->stopQueueResource = $stopQueueResource;
    }

    public function create(\M2E\TikTokShop\Model\StopQueue $stopQueue): void
    {
        $this->stopQueueResource->save($stopQueue);
    }

    public function save(\M2E\TikTokShop\Model\StopQueue $stopQueue): void
    {
        $this->stopQueueResource->save($stopQueue);
    }

    /**
     * @param int $limit
     *
     * @return \M2E\TikTokShop\Model\StopQueue[]
     */
    public function findNotProcessed(int $limit): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ResourceModel::COLUMN_IS_PROCESSED, 0);
        $collection->setOrder(ResourceModel::COLUMN_CREATE_DATE, \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $collection->getSelect()->limit($limit);

        return array_values($collection->getItems());
    }

    public function deleteCompletedAfterBorderDate(\DateTime $borderDate): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            [
                ResourceModel::COLUMN_IS_PROCESSED . ' = ?' => 1,
                ResourceModel::COLUMN_UPDATE_DATE . ' < ?' => $borderDate->format('Y-m-d H:i:s'),
            ]
        );
    }
}
