<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\Order\ReturnRequest\DeclineReasons;

use M2E\TikTokShop\Model\Channel\Connector\Order\ReturnRequest\DeclineReasons\Command;

class Retriever
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\TikTokShop\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    public function process(\M2E\TikTokShop\Model\Order\Item $item): Command\Response
    {
        $command = new Command(
            $item->getAccount()->getServerHash(),
            $item->getOrder()->getShop()->getShopId(),
            $item->getOrder()->getTtsOrderId(),
            $item->getRefundReturnId()
        );

        /** @var \M2E\TikTokShop\Model\Channel\Connector\Order\ReturnRequest\DeclineReasons\Command\Response $response */
        $response = $this->singleClient->process($command);

        return $response;
    }
}
