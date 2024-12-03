<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion\Product;

use M2E\TikTokShop\Model\ResourceModel\Promotion\Product as PromotionProduct;
use M2E\TikTokShop\Model\ResourceModel\Promotion as Promotion;
use M2E\TikTokShop\Model\Promotion as PromotionModel;

class Repository
{
    private PromotionProduct\CollectionFactory $collectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Promotion\Product $resource;
    private \M2E\TikTokShop\Model\Promotion\ProductFactory $productFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Promotion $resourcePromotion;

    public function __construct(
        \M2E\TikTokShop\Model\Promotion\ProductFactory $productFactory,
        PromotionProduct\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Promotion\Product $resource,
        \M2E\TikTokShop\Model\ResourceModel\Promotion $resourcePromotion
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->productFactory = $productFactory;
        $this->resourcePromotion = $resourcePromotion;
    }

    public function create(\M2E\TikTokShop\Model\Promotion\Product $promotionProduct): void
    {
        $this->resource->save($promotionProduct);
    }

    public function save(\M2E\TikTokShop\Model\Promotion\Product $promotionProduct): void
    {
        $this->resource->save($promotionProduct);
    }

    public function remove(\M2E\TikTokShop\Model\Promotion\Product $promotionProduct): void
    {
        $this->resource->delete($promotionProduct);
    }

    public function find(int $id): ?\M2E\TikTokShop\Model\Promotion\Product
    {
        $promotionProduct = $this->productFactory->createEmpty();

        $this->resource->load($promotionProduct, $id);

        if ($promotionProduct->isObjectNew()) {
            return null;
        }

        return $promotionProduct;
    }

    public function get(int $id): \M2E\TikTokShop\Model\Promotion\Product
    {
        $promotionProduct = $this->find($id);
        if ($promotionProduct === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Promotion product not found.');
        }

        return $promotionProduct;
    }

    /**
     * @return \M2E\TikTokShop\Model\Promotion\Product[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }

    /**
     * @param int $promotionId
     *
     * @return \M2E\TikTokShop\Model\Promotion\Product[]
     */
    public function findProductsByPromotion(int $promotionId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            PromotionProduct::COLUMN_PROMOTION_ID,
            $promotionId,
        );

        return array_values($collection->getItems());
    }

    /**
     * @param int $promotionId
     *
     * @return \M2E\TikTokShop\Model\Promotion\Product[]
     */
    public function findWithLevelVariantByPromotionId(int $promotionId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            PromotionProduct::COLUMN_PROMOTION_ID,
            $promotionId
        );

        $collection->addFieldToFilter(
            PromotionProduct::COLUMN_SKU_ID,
            ['notnull' => true]
        );

        return array_values($collection->getItems());
    }

    public function removeByPromotion(\M2E\TikTokShop\Model\Promotion $promotion): void
    {
        $collection = $this->collectionFactory->create();
        $collection->getConnection()->delete(
            $collection->getMainTable(),
            [PromotionProduct::COLUMN_PROMOTION_ID . ' =?' => $promotion->getId()]
        );
    }

    public function isExistPromotionProductByProductIds(array $productIds): bool
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(PromotionProduct::COLUMN_PRODUCT_ID, ['in' => $productIds]);

        return !empty($collection->getItems());
    }

    /**
     * @param \M2E\TikTokShop\Model\Promotion $promotion
     * @param array $actualProductIds
     *
     * @return \M2E\TikTokShop\Model\Promotion\Product[]
     */
    public function findOldProducts(\M2E\TikTokShop\Model\Promotion $promotion, array $actualProductIds): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            PromotionProduct::COLUMN_PROMOTION_ID,
            $promotion->getId()
        );

        if (!empty($actualProductIds)) {
            $collection->addFieldToFilter(
                PromotionProduct::COLUMN_PRODUCT_ID,
                ['nin' => $actualProductIds],
            );
        }

        return array_values($collection->getItems());
    }

    /**
     * @param \M2E\TikTokShop\Model\Promotion $promotion
     * @param array $actualSkuIds
     *
     * @return \M2E\TikTokShop\Model\Promotion\Product[]
     */
    public function findOldSkus(\M2E\TikTokShop\Model\Promotion $promotion, array $actualSkuIds): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            PromotionProduct::COLUMN_PROMOTION_ID,
            $promotion->getId()
        );
        $collection->addFieldToFilter(
            PromotionProduct::COLUMN_SKU_ID,
            ['nin' => $actualSkuIds],
        );

        return array_values($collection->getItems());
    }

    public function isExistActiveOrNotStartPromotionForProduct(string $channelProductId, int $accountId, int $shopId): bool
    {
        $now = \M2E\TikTokShop\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');

        $collection = $this->collectionFactory->create();

        $collection->join(
            ['p' => $this->resourcePromotion->getMainTable()],
            sprintf(
                '`p`.%s = `main_table`.%s',
                Promotion::COLUMN_ID,
                PromotionProduct::COLUMN_PROMOTION_ID,
            ),
            [],
        );

        $collection->addFieldToFilter('main_table.' . PromotionProduct::COLUMN_PRODUCT_ID, $channelProductId);
        $collection->addFieldToFilter('main_table.' . PromotionProduct::COLUMN_ACCOUNT_ID, $accountId);
        $collection->addFieldToFilter('main_table.' . PromotionProduct::COLUMN_SHOP_ID, $shopId);
        $collection->addFieldToFilter('p.' . Promotion::COLUMN_END_DATE, ['gteq' => $now]);
        $collection->addFieldToFilter(
            'p.' . Promotion::COLUMN_STATUS,
            ['in' => [PromotionModel::STATUS_NOT_START, PromotionModel::STATUS_ONGOING]]
        );
        $collection->addFieldToFilter('p.' . Promotion::COLUMN_PRODUCT_LEVEL, PromotionModel::PRODUCT_LEVEL_BY_PRODUCT);

        return (bool)$collection->getSize();
    }

    public function isExistActiveOrNotStartPromotionForSku(string $skuId, int $accountId, int $shopId): bool
    {
        $now = \M2E\TikTokShop\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');

        $collection = $this->collectionFactory->create();

        $collection->join(
            ['p' => $this->resourcePromotion->getMainTable()],
            sprintf(
                '`p`.%s = `main_table`.%s',
                Promotion::COLUMN_ID,
                PromotionProduct::COLUMN_PROMOTION_ID,
            ),
            [],
        );

        $collection->addFieldToFilter('main_table.' . PromotionProduct::COLUMN_SKU_ID, $skuId);
        $collection->addFieldToFilter('main_table.' . PromotionProduct::COLUMN_ACCOUNT_ID, $accountId);
        $collection->addFieldToFilter('main_table.' . PromotionProduct::COLUMN_SHOP_ID, $shopId);
        $collection->addFieldToFilter(
            'p.' . Promotion::COLUMN_STATUS,
            ['in' => [PromotionModel::STATUS_NOT_START, PromotionModel::STATUS_ONGOING]]
        );

        $collection->addFieldToFilter('p.' . Promotion::COLUMN_END_DATE, ['gteq' => $now]);

        return (bool)$collection->getSize();
    }
}
