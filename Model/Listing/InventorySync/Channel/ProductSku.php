<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync\Channel;

class ProductSku
{
    private string $skuId;
    private string $sku;
    private string $currency;
    private float $price;
    private int $qty;
    private string $warehouseId;
    private ?\M2E\TikTokShop\Model\Product\VariantSku\Identifier $identifier;
    private array $salesAttributes;

    public function __construct(
        string $skuId,
        string $sku,
        string $currency,
        float $price,
        int $qty,
        string $warehouseId,
        ?\M2E\TikTokShop\Model\Product\VariantSku\Identifier $identifier,
        array $salesAttributes
    ) {
        $this->skuId = $skuId;
        $this->sku = $sku;
        $this->currency = $currency;
        $this->price = $price;
        $this->qty = $qty;
        $this->warehouseId = $warehouseId;
        $this->identifier = $identifier;
        $this->salesAttributes = $salesAttributes;
    }

    public function getSkuId(): string
    {
        return $this->skuId;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    public function getWarehouseId(): string
    {
        return $this->warehouseId;
    }

    public function getStatus(): int
    {
        if ($this->getQty() === 0) {
            return \M2E\TikTokShop\Model\Product::STATUS_INACTIVE;
        }

        return \M2E\TikTokShop\Model\Product::STATUS_LISTED;
    }

    public function getIdentifier(): ?\M2E\TikTokShop\Model\Product\VariantSku\Identifier
    {
        return $this->identifier;
    }

    public function getSalesAttributes(): array
    {
        return $this->salesAttributes;
    }
}
