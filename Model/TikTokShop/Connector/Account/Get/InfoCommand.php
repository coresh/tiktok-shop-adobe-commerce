<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Account\Get;

class InfoCommand implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    private string $accountHash;

    public function __construct(string $accountHash)
    {
        $this->accountHash = $accountHash;
    }

    public function getCommand(): array
    {
        return ['account', 'get', 'info'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
        ];
    }

    public function parseResponse(\M2E\TikTokShop\Model\Connector\Response $response): Status
    {
        return new Status(
            (bool)$response->getResponseData()['info']['status'],
            $response->getResponseData()['info']['note'],
        );
    }
}
