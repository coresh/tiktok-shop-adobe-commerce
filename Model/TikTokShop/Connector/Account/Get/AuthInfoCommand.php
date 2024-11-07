<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Account\Get;

class AuthInfoCommand implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    /** @var string[] */
    private array $accountsServerHashes;

    public function __construct(
        array $accountsServerHashes
    ) {
        $this->accountsServerHashes = $accountsServerHashes;
    }

    public function getRequestData(): array
    {
        return ['accounts' => $this->accountsServerHashes];
    }

    public function getCommand(): array
    {
        return ['account', 'get', 'authInfo'];
    }

    public function parseResponse(\M2E\TikTokShop\Model\Connector\Response $response): object
    {
        $accountsInfo = $response->getResponseData()['accounts'];

        $result = new Result();
        foreach ($this->accountsServerHashes as $hash) {
            if (!isset($accountsInfo[$hash])) {
                continue;
            }

            $isValid = $accountsInfo[$hash]['is_valid'];
            $result->addAccount($hash, $isValid);
        }

        return $result;
    }
}
