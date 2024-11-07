<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity;

class Command implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity\Order $order;
    private string $accountHash;
    private string $shopId;
    private string $shippingProviderId;
    private string $trackingNumber;

    public function __construct(
        string $accountHash,
        string $shopId,
        string $shippingProviderId,
        string $trackingNumber,
        \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity\Order $order
    ) {
        $this->order = $order;
        $this->accountHash = $accountHash;
        $this->shopId = $shopId;
        $this->shippingProviderId = $shippingProviderId;
        $this->trackingNumber = $trackingNumber;
    }

    public function getCommand(): array
    {
        return ['Order', 'Send', 'Entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'shop_id' => $this->shopId,
            'shipping_provider_id' => $this->shippingProviderId,
            'tracking_number' => $this->trackingNumber,
            'order' => [
                'id' => $this->order->id,
                'items' => $this->order->orderItems,
            ]
        ];
    }

    public function parseResponse(
        \M2E\TikTokShop\Model\Connector\Response $response
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity\Response {
        $errorMessages = [];
        $warningMessages = [];

        foreach ($response->getMessageCollection()->getMessages() as $message) {
            if ($message->isError()) {
                $errorMessages[] = $message;
            }

            if ($message->isWarning()) {
                $warningMessages[] = $message;
            }
        }

        $packageId = $response->getResponseData()['package_id'] ?? null;

        return new Response(
            empty($errorMessages),
            $packageId,
            $errorMessages,
            $warningMessages
        );
    }
}
