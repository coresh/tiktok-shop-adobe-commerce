<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Account\Update;

class Processor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\TikTokShop\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(
        \M2E\TikTokShop\Model\Account $account,
        string $authCode
    ): Response {
        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Account\UpdateCommand(
            $account->getServerHash(),
            $authCode,
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
