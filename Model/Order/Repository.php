<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order;

use M2E\TikTokShop\Model\ResourceModel\Order as OrderResource;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory;
    private OrderResource $orderResource;
    private \M2E\TikTokShop\Model\OrderFactory $orderFactory;

    public function __construct(
        OrderResource $orderResource,
        \M2E\TikTokShop\Model\OrderFactory $orderFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order\Change\CollectionFactory $orderChangeCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order\Note\CollectionFactory $orderNoteCollectionFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->orderChangeCollectionFactory = $orderChangeCollectionFactory;
        $this->orderNoteCollectionFactory = $orderNoteCollectionFactory;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
    }

    public function get(int $id): \M2E\TikTokShop\Model\Order
    {
        $order = $this->find($id);
        if ($order === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic("Order $id not found.");
        }

        return $order;
    }

    public function find(int $id): ?\M2E\TikTokShop\Model\Order
    {
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $id);

        if ($order->isObjectNew()) {
            return null;
        }

        return $order;
    }

    public function findByMagentoOrderId(int $id): ?\M2E\TikTokShop\Model\Order
    {
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $id, OrderResource::COLUMN_MAGENTO_ORDER_ID);

        if ($order->isObjectNew()) {
            return null;
        }

        return $order;
    }

    public function removeByAccountId(int $accountId): void
    {
        $this->removeRelatedOrderChangesByAccountId($accountId);
        $this->removeRelatedOrderItemsByAccountId($accountId);
        $this->removeRelatedOrderNoteByAccountId($accountId);

        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->getConnection()->delete(
            $orderCollection->getMainTable(),
            ['account_id = ?' => $accountId]
        );
    }

    private function removeRelatedOrderItemsByAccountId(int $accountId): void
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            OrderResource::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $orderCollection->getSelect()
                        ->reset('columns')
                        ->columns('id');

        $orderItemCollection = $this->orderItemCollectionFactory->create();
        $orderItemCollection->getConnection()->delete(
            $orderItemCollection->getMainTable(),
            [
                \M2E\TikTokShop\Model\ResourceModel\Order\Item::COLUMN_ORDER_ID . ' IN (?)'
                => $orderCollection->getSelect(),
            ]
        );
    }

    private function removeRelatedOrderChangesByAccountId(int $accountId): void
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            OrderResource::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $orderCollection->getSelect()
                        ->reset('columns')
                        ->columns('id');

        $orderChangeCollection = $this->orderChangeCollectionFactory->create();
        $orderChangeCollection->getConnection()->delete(
            $orderChangeCollection->getMainTable(),
            [
                \M2E\TikTokShop\Model\ResourceModel\Order\Change::COLUMN_ORDER_ID . ' IN (?)'
                => $orderCollection->getSelect(),
            ]
        );
    }

    private function removeRelatedOrderNoteByAccountId(int $accountId): void
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter(
            OrderResource::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $orderCollection->getSelect()
                        ->reset('columns')
                        ->columns('id');

        $orderNoteCollection = $this->orderNoteCollectionFactory->create();
        $orderNoteCollection->getConnection()->delete(
            $orderNoteCollection->getMainTable(),
            [
                \M2E\TikTokShop\Model\ResourceModel\Order\Note::COLUMN_ORDER_ID . ' IN (?)'
                => $orderCollection->getSelect(),
            ]
        );
    }

    public function findOneByAccountIdAndTtsOrderId(
        int $accountId,
        string $ttsOrderId
    ): ?\M2E\TikTokShop\Model\Order {
        $collection = $this->orderCollectionFactory->create();

        $collection->addFieldToFilter(OrderResource::COLUMN_ACCOUNT_ID, ['eq' => $accountId]);
        $collection->addFieldToFilter(OrderResource::COLUMN_TTS_ORDER_ID, ['eq' => $ttsOrderId]);
        $collection->setOrder(OrderResource::COLUMN_ID);
        $collection->setPageSize(1);

        $order = $collection->getFirstItem();

        if ($order->isObjectNew()) {
            return null;
        }

        return $order;
    }

    public function save(\M2E\TikTokShop\Model\Order $order): void
    {
        $this->orderResource->save($order);
    }

    /**
     * @param array $orderIds
     *
     * @return \M2E\TikTokShop\Model\Order[]
     */
    public function findByIds(array $orderIds): array
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter(OrderResource::COLUMN_ID, ['in' => $orderIds]);

        return array_values($collection->getItems());
    }
}
