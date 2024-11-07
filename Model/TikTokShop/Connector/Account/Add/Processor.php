<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Account\Add;

class Processor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\TikTokShop\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(string $authCode, string $region): Response
    {
        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Account\AddCommand($authCode, $region);

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
