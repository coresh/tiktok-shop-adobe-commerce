<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync\Channel;

class ProductSkuCollection
{
    /** @var \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSku[] */
    private array $variants = [];

    public function add(ProductSku $variant): void
    {
        $this->variants[$variant->getSkuId()] = $variant;
    }

    /**
     * @return \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSku[]
     */
    public function getAll(): array
    {
        return array_values($this->variants);
    }

    public function getFirst(): \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSku
    {
        return reset($this->variants);
    }

    public function count(): int
    {
        return count($this->variants);
    }

    public function findProductSkuBySkuId(string $skuId): ?\M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSku
    {
        return $this->variants[$skuId] ?? null;
    }
}
