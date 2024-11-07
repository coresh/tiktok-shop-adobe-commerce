<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\ItemsByUpdateDate;

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
        \DateTimeInterface $updateFrom,
        \DateTimeInterface $updateTo
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\Response {
        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\ItemsByUpdateDateCommand(
            $account->getServerHash(),
            $shop->getShopId(),
            $updateFrom,
            $updateTo,
        );

        /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\Response */
        return $this->singleClient->process($command);
    }
}
