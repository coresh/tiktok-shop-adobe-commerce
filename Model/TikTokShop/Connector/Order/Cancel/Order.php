<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\Cancel;

class Order
{
    private string $orderId;
    private array $orderLineItemIds = [];
    private array $skusData = [];

    public function __construct(
        string $orderId
    ) {
        $this->orderId = $orderId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderLineItemIds(array $ids): void
    {
        $this->orderLineItemIds = $ids;
    }

    public function addOrderLineItemId(string $id): void
    {
        $this->orderLineItemIds[] = $id;
    }

    public function getOrderLineItemIds(): array
    {
        return $this->orderLineItemIds;
    }

    public function addSku(string $skuId, int $qty): void
    {
        if (!isset($this->skusData[$skuId])) {
            $this->skusData[$skuId] = 0;
        }

        $this->skusData[$skuId] += $qty;
    }

    public function getSkusData(): array
    {
        $result = [];
        foreach ($this->skusData as $skuId => $qty) {
            $result[] = [
                'qty' => $qty,
                'id' => (string)$skuId,
            ];
        }

        return $result;
    }
}
