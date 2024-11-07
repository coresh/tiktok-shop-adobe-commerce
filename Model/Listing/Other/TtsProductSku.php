<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Other;

class TtsProductSku
{
    private string $skuId;
    private string $sku;
    private string $currency;
    private float $price;
    private int $qty;
    private string $warehouseId;
    private array $inventoryData;
    private ?\M2E\TikTokShop\Model\Product\VariantSku\Identifier $identifier;

    public function __construct(
        string $skuId,
        string $sku,
        string $currency,
        float $price,
        int $qty,
        string $warehouseId,
        array $inventoryData,
        ?\M2E\TikTokShop\Model\Product\VariantSku\Identifier $identifier
    ) {
        $this->skuId = $skuId;
        $this->sku = $sku;
        $this->currency = $currency;
        $this->price = $price;
        $this->qty = $qty;
        $this->warehouseId = $warehouseId;
        $this->inventoryData = $inventoryData;
        $this->identifier = $identifier;
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

    public function getInventoryData(): array
    {
        return $this->inventoryData;
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
}
