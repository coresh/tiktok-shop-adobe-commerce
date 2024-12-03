<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion;

use M2E\TikTokShop\Model\Promotion;
use M2E\TikTokShop\Model\ResourceModel\Promotion as PromotionResource;

class Repository
{
    private \M2E\TikTokShop\Model\PromotionFactory $promotionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Promotion\CollectionFactory $collectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Promotion $resource;

    public function __construct(
        \M2E\TikTokShop\Model\PromotionFactory $promotionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Promotion\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Promotion $resource
    ) {
        $this->promotionFactory = $promotionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
    }

    public function create(\M2E\TikTokShop\Model\Promotion $promotion): void
    {
        $this->resource->save($promotion);
    }

    public function save(\M2E\TikTokShop\Model\Promotion $promotion): void
    {
        $this->resource->save($promotion);
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function remove(\M2E\TikTokShop\Model\Promotion $promotion): void
    {
        $this->resource->delete($promotion);
    }

    public function find(int $id): ?\M2E\TikTokShop\Model\Promotion
    {
        $promotion = $this->promotionFactory->create();
        $this->resource->load($promotion, $id);

        if ($promotion->isObjectNew()) {
            return null;
        }

        return $promotion;
    }

    public function get(int $id): \M2E\TikTokShop\Model\Promotion
    {
        $promotion = $this->find($id);
        if ($promotion === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Promotion not found.');
        }

        return $promotion;
    }

    /**
     * @return \M2E\TikTokShop\Model\Promotion[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }

    public function findByAccountAndShop(int $accountId, int $shopId): PromotionCollection
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(PromotionResource::COLUMN_ACCOUNT_ID, $accountId);
        $collection->addFieldToFilter(PromotionResource::COLUMN_SHOP_ID, $shopId);

        $result = new PromotionCollection();
        foreach ($collection->getItems() as $item) {
            $result->add($item);
        }

        return $result;
    }
}
