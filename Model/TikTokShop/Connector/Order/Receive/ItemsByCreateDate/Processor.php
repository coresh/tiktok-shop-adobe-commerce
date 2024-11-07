<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\ItemsByCreateDate;

class Processor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $singleClient;

    public function __construct(\M2E\TikTokShop\Model\Connector\Client\Single $singleClient)
    {
        $this->singleClient = $singleClient;
    }

    public function process(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        \DateTimeInterface $createFrom,
        \DateTimeInterface $createTo
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\Response {
        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\ItemsByCreateDateCommand(
            $account->getServerHash(),
            $shop->getShopId(),
            $createFrom,
            $createTo,
        );

        /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\Response */
        return $this->singleClient->process($command);
    }
}
