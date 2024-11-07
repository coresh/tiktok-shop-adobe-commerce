<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Attribute\Get;

class Processor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\TikTokShop\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        string $categoryId
    ): Response {
        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Attribute\GetCommand(
            $account->getServerHash(),
            $shop->getShopId(),
            $categoryId,
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
