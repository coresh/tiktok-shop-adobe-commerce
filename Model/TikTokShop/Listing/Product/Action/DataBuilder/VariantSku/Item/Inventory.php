<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\VariantSku\Item;

class Inventory
{
    private string $warehouseId;
    private int $quantity;

    public function __construct(string $warehouseId, int $quantity)
    {
        $this->warehouseId = $warehouseId;
        $this->quantity = $quantity;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function toArray(): array
    {
        return [
            'warehouse_id' => $this->warehouseId,
            'quantity' => $this->quantity,
        ];
    }
}
