<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\RecommendedCategory\Get;

class Processor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $singleClient;

    public function __construct(
        \M2E\TikTokShop\Model\Connector\Client\Single $singleClient
    ) {
        $this->singleClient = $singleClient;
    }

    public function process(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        string $productTitle
    ): Response {
        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\RecommendedCategory\GetCommand(
            $account->getServerHash(),
            $shop->getShopId(),
            $productTitle
        );

        /** @var Response */
        return $this->singleClient->process($command);
    }
}
