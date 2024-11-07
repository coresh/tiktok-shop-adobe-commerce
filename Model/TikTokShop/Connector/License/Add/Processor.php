<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\License\Add;

class Processor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;

    public function __construct(\M2E\TikTokShop\Model\Connector\Client\Single $serverClient)
    {
        $this->serverClient = $serverClient;
    }

    public function process(Request $request): Response
    {
        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\License\AddCommand(
            $request
        );

        /** @var Response */
        return $this->serverClient->process($command);
    }
}
