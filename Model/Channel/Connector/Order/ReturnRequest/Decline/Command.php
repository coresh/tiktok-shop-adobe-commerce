<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\Connector\Order\ReturnRequest\Decline;

use M2E\TikTokShop\Model\Channel\Order\ReturnRequest\Order;

class Command implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $shopId;
    /** @var \M2E\TikTokShop\Model\Channel\Order\ReturnRequest\Order */
    private Order $order;

    public function __construct(
        string $accountHash,
        string $shopId,
        Order $order
    ) {
        $this->accountHash = $accountHash;
        $this->shopId = $shopId;
        $this->order = $order;
    }

    public function getCommand(): array
    {
        return ['order', 'return', 'decline'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'shop_id' => $this->shopId,
            'order_id' => $this->order->getOrderId(),
            'items' => $this->order->getOrderItems()
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): object
    {
        return $response;
    }
}
