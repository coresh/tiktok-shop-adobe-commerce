<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity;

class Order
{
    public string $id;
    public array $orderItems;

    public function __construct(
        string $orderId,
        array $orderItems
    ) {
        $this->id = $orderId;
        $this->orderItems = $orderItems;
    }
}
