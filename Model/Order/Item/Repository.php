<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Item;

use M2E\TikTokShop\Model\ResourceModel\Order\Item as OrderItemResource;

class Repository
{
    public const COLUMN_GROUPED_QTY = 'total_qty';
    public const COLUMN_GROUPED_ITEM_IDS = 'order_items_ids';

    private \M2E\TikTokShop\Model\ResourceModel\Order\Item\CollectionFactory $collectionFactory;
    private OrderItemResource $resource;
    private \M2E\TikTokShop\Model\Order\ItemFactory $itemFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Order\ItemFactory $itemFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order\Item\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order\Item $resource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->itemFactory = $itemFactory;
    }

    public function find(int $id): ?\M2E\TikTokShop\Model\Order\Item
    {
        $item = $this->itemFactory->createEmpty();
        $this->resource->load($item, $id);

        if ($item->isObjectNew()) {
            return null;
        }

        return $item;
    }

    /**
     * @return \M2E\TikTokShop\Model\Order\Item[]
     */
    public function getByIds(array $ids): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(OrderItemResource::COLUMN_ID, ['in' => $ids]);

        return array_values($collection->getItems());
    }

    public function create(\M2E\TikTokShop\Model\Order\Item $orderItem): void
    {
        $this->resource->save($orderItem);
    }

    public function save(\M2E\TikTokShop\Model\Order\Item $orderItem): void
    {
        $this->resource->save($orderItem);
    }

    public function findByOrder(\M2E\TikTokShop\Model\Order $order): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(OrderItemResource::COLUMN_ORDER_ID, ['eq' => $order->getId()]);

        $result = [];
        foreach ($collection->getItems() as $item) {
            $item->setOrder($order);

            $result[] = $item;
        }

        return $result;
    }

    public function findByOrderIdAndItemId(int $orderId, string $ttsItemId): ?\M2E\TikTokShop\Model\Order\Item
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(OrderItemResource::COLUMN_ORDER_ID, ['eq' => $orderId]);
        $collection->addFieldToFilter(OrderItemResource::COLUMN_TTS_ITEM_ID, ['eq' => $ttsItemId]);

        $item = $collection->getFirstItem();

        if ($item->isObjectNew()) {
            return null;
        }

        return $item;
    }

    public function getGroupOrderItemCollection(int $orderId): \M2E\TikTokShop\Model\ResourceModel\Order\Item\Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(OrderItemResource::COLUMN_ORDER_ID, ['eq' => $orderId]);

        $collection->getSelect()->group([
            OrderItemResource::COLUMN_TTS_PRODUCT_ID,
            OrderItemResource::COLUMN_TTS_SKU_ID,
        ]);

        $collection->getSelect()->columns([
            self::COLUMN_GROUPED_QTY => new \Zend_Db_Expr(
                sprintf('SUM(%s)', OrderItemResource::COLUMN_QTY_PURCHASED)
            ),
            self::COLUMN_GROUPED_ITEM_IDS => new \Zend_Db_Expr(
                sprintf('GROUP_CONCAT(%s)', OrderItemResource::COLUMN_ID)
            )
        ]);

        return $collection;
    }
}
