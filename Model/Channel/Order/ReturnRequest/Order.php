<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\Order\ReturnRequest;

use M2E\TikTokShop\Model\Channel\Order\ReturnRequest\Order\Item as ReturnOrderItem;

class Order
{
    private string $orderId;
    /** @var \M2E\TikTokShop\Model\Channel\Order\ReturnRequest\Order\Item[] */
    private array $orderItems;

    public function __construct(
        string $orderId
    ) {
        $this->orderId = $orderId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function addOrderItem(ReturnOrderItem $item): void
    {
        $this->orderItems[] = $item;
    }

    public function getOrderItems(): array
    {
        $result = [];
        foreach ($this->orderItems as $orderItem) {
            $item = [
                'id' => $orderItem->getItemId(),
                'refund_return_id' => $orderItem->getRefundReturnId(),
            ];

            $reason = $orderItem->getReason();
            if ($reason !== null) {
                $item['reason'] = $reason;
            }

            $result[] = $item;
        }

        return $result;
    }
}
