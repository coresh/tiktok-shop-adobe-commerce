<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest\Decline;

class Processor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\TikTokShop\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    public function process(
        \M2E\TikTokShop\Model\Order $order,
        string $reason
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest\Response {
        $command = new Command(
            $order->getAccount()->getServerHash(),
            $order->getShop()->getShopId(),
            $order->getTtsOrderId(),
            $reason
        );

        /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Order\CancellationRequest\Response $response */
        $response = $this->singleClient->process($command);

        return $response;
    }
}
