<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion;

use M2E\TikTokShop\Model\ResourceModel\Promotion\Product as ProductPromotion;

class Product extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ProductPromotion::class);
    }

    public function createAsProduct(
        \M2E\TikTokShop\Model\Promotion $promotion,
        string $productId,
        ?float $fixedPrice,
        ?string $discount,
        int $quantityLimit,
        int $perUser
    ): self {
        $this
            ->setPromotionId($promotion->getId())
            ->setAccountId($promotion->getAccountId())
            ->setShopId($promotion->getShopId())
            ->setProductId($productId)
            ->setFixedPrice($fixedPrice)
            ->setDiscount($discount)
            ->setQuantityLimit($quantityLimit)
            ->setPerUser($perUser);

        return $this;
    }

    public function createAsSku(
        \M2E\TikTokShop\Model\Promotion $promotion,
        string $productId,
        string $skuId,
        ?float $skuFixedPrice,
        ?string $skuDiscount,
        int $skuQuantityLimit,
        int $skuPerUser
    ): self {
        $this
            ->setPromotionId($promotion->getId())
            ->setAccountId($promotion->getAccountId())
            ->setShopId($promotion->getShopId())
            ->setProductId($productId)
            ->setSkuId($skuId)
            ->setSkuFixedPrice($skuFixedPrice)
            ->setSkuDiscount($skuDiscount)
            ->setSkuQuantityLimit($skuQuantityLimit)
            ->setSkuPerUser($skuPerUser);

        return $this;
    }

    private function setPromotionId(int $promotionId): self
    {
        $this->setData(ProductPromotion::COLUMN_PROMOTION_ID, $promotionId);

        return $this;
    }

    private function setAccountId(int $accountId): self
    {
        $this->setData(ProductPromotion::COLUMN_ACCOUNT_ID, $accountId);

        return $this;
    }

    private function setShopId(int $shopId): self
    {
        $this->setData(ProductPromotion::COLUMN_SHOP_ID, $shopId);

        return $this;
    }

    public function getPromotionId(): int
    {
        return (int)$this->getData(ProductPromotion::COLUMN_PROMOTION_ID);
    }

    public function setProductId(string $productId): self
    {
        $this->setData(ProductPromotion::COLUMN_PRODUCT_ID, $productId);

        return $this;
    }

    public function getProductId(): string
    {
        return $this->getData(ProductPromotion::COLUMN_PRODUCT_ID);
    }

    public function setFixedPrice(?float $fixedPrice): self
    {
        if ($fixedPrice !== null) {
            $this->setData(ProductPromotion::COLUMN_PRODUCT_FIXED_PRICE, $fixedPrice);
        }

        return $this;
    }

    public function setDiscount(?string $discount): self
    {
        if ($discount !== null) {
            $this->setData(ProductPromotion::COLUMN_PRODUCT_DISCOUNT, $discount);
        }

        return $this;
    }

    public function setQuantityLimit(int $quantityLimit): self
    {
        $this->setData(ProductPromotion::COLUMN_PRODUCT_QUANTITY_LIMIT, $quantityLimit);

        return $this;
    }

    public function setPerUser(int $perUser): self
    {
        $this->setData(ProductPromotion::COLUMN_PRODUCT_PER_USER, $perUser);

        return $this;
    }

    public function setSkuId(string $skuId): self
    {
        $this->setData(ProductPromotion::COLUMN_SKU_ID, $skuId);

        return $this;
    }

    public function setSkuFixedPrice(?float $fixedPrice): self
    {
        if ($fixedPrice !== null) {
            $this->setData(ProductPromotion::COLUMN_SKU_FIXED_PRICE, $fixedPrice);
        }

        return $this;
    }

    public function setSkuDiscount(?string $discount): self
    {
        if ($discount !== null) {
            $this->setData(ProductPromotion::COLUMN_SKU_DISCOUNT, $discount);
        }

        return $this;
    }

    public function setSkuQuantityLimit(int $quantityLimit): self
    {
        $this->setData(ProductPromotion::COLUMN_SKU_QUANTITY_LIMIT, $quantityLimit);

        return $this;
    }

    public function setSkuPerUser(int $perUser): self
    {
        $this->setData(ProductPromotion::COLUMN_SKU_PER_USER, $perUser);

        return $this;
    }

    public function getSkuId(): ?string
    {
        return $this->getData(ProductPromotion::COLUMN_SKU_ID);
    }
}
