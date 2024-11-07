<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\VariantSku;

class OnlineData
{
    private int $variantId;
    private int $qty;
    private float $price;
    private ?string $sku;

    public function __construct(
        int $variantId,
        int $qty,
        float $price,
        ?string $sku
    ) {
        $this->variantId = $variantId;
        $this->qty = $qty;
        $this->price = $price;
        $this->sku = $sku;
    }

    public function getVariantId(): int
    {
        return $this->variantId;
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }
}
