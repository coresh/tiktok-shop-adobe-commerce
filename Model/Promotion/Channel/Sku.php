<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Promotion\Channel;

class Sku
{
    private ?string $skuId;
    private ?float $price;
    private ?string $discount;
    private int $quantityLimit;
    private int $perUser;

    public function __construct(
        ?string $skuId,
        ?float $price,
        ?string $discount,
        int $quantityLimit,
        int $perUser
    ) {
        $this->skuId = $skuId;
        $this->price = $price;
        $this->discount = $discount;
        $this->quantityLimit = $quantityLimit;
        $this->perUser = $perUser;
    }

    public function getSkuId(): ?string
    {
        return $this->skuId;
    }

    public function getPrice(): ?float
    {
        return $this->price;
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
}
