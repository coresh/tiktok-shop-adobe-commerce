<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity;

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
        string $shippingProviderId,
        string $trackingNumber,
        \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity\Order $order
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity\Response {
        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity\Command(
            $account->getServerHash(),
            $shop->getShopId(),
            $shippingProviderId,
            $trackingNumber,
            $order,
        );

        /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity\Response */
        return $this->singleClient->process($command);
    }
}
