<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Category\Get;

class Processor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\TikTokShop\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop
    ): Response {
        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Category\GetCommand(
            $account->getServerHash(),
            $shop->getShopId(),
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
