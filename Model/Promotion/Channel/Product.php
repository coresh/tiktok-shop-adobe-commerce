<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion\Channel;

class Product
{
    private string $productId;
    private ?float $fixedPrice;
    private ?string $discount;
    private int $quantityLimit;
    private int $perUser;

    /** @var \M2E\TikTokShop\Model\Promotion\Channel\Sku[] */
    private array $skus;

    public function __construct(
        string $productId,
        ?float $fixedPrice,
        ?string $discount,
        int $quantityLimit,
        int $perUser,
        array $skus
    ) {
        $this->productId = $productId;
        $this->fixedPrice = $fixedPrice;
        $this->discount = $discount;
        $this->quantityLimit = $quantityLimit;
        $this->perUser = $perUser;
        $this->skus = $skus;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getFixedPrice(): ?float
    {
        return $this->fixedPrice;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function getQuantityLimit(): int
    {
        return $this->quantityLimit;
    }

    public function getPerUser(): int
    {
        return $this->perUser;
    }

    /**
     * @return \M2E\TikTokShop\Model\Promotion\Channel\Sku[]
     */
    public function getPromotionProductSkus(): array
    {
        return $this->skus;
    }
}
