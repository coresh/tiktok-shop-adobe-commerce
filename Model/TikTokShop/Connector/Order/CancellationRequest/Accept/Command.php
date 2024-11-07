<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest\Accept;

class Command implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    private string $account;
    private string $shopId;
    private string $orderId;

    public function __construct(string $account, string $shopId, string $orderId)
    {
        $this->account = $account;
        $this->shopId = $shopId;
        $this->orderId = $orderId;
    }

    public function getCommand(): array
    {
        return ['order', 'cancellationRequest', 'approve'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->account,
            'shop_id' => $this->shopId,
            'order_id' => $this->orderId,
        ];
    }

    public function parseResponse(
        \M2E\TikTokShop\Model\Connector\Response $response
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest\Response {
        $orderData = $response->getResponseData()['order'] ?? null;

        $order = null;
        if ($orderData !== null) {
            $order = new \M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest\Order(
                $orderData['status'],
                (bool)$orderData['is_buyer_request_cancel'],
                $orderData['cancel_reason'] ?? '',
            );
        }

        return new \M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest\Response(
            $response->getMessageCollection(),
            $order
        );
    }
}
