<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\Cancel;

class EntityCommand implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $shopId;
    /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Cancel\Order */
    private Order $order;
    private string $reason;

    public function __construct(
        string $accountHash,
        string $shopId,
        Order $order,
        string $reason
    ) {
        $this->accountHash = $accountHash;
        $this->shopId = $shopId;
        $this->order = $order;
        $this->reason = $reason;
    }

    public function getCommand(): array
    {
        return ['order', 'cancel', 'entity'];
    }

    public function getRequestData(): array
    {
        if (empty($this->order->getSkusData())) {
            throw new \LogicException('Unable to cancel order because no order skus data.');
        }

        return [
            'account' => $this->accountHash,
            'shop_id' => $this->shopId,
            'order' => [
                'id' => $this->order->getOrderId(),
                'order_line_item_ids' => $this->order->getOrderLineItemIds(),
                'skus' => $this->order->getSkusData(),
            ],
            'reason' => $this->reason,
        ];
    }

    public function parseResponse(\M2E\TikTokShop\Model\Connector\Response $response): object
    {
        return $response;
    }
}
