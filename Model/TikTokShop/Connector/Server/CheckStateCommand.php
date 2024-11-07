<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Server;

class CheckStateCommand implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    public function getCommand(): array
    {
        return ['server', 'check', 'state'];
    }

    public function getRequestData(): array
    {
        return [];
    }

    public function parseResponse(
        \M2E\TikTokShop\Model\Connector\Response $response
    ): \M2E\TikTokShop\Model\Connector\Response {
        return $response;
    }
}
