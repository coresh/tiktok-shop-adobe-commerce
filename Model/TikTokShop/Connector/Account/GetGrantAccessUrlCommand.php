<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Account;

class GetGrantAccessUrlCommand implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    private string $backUrl;
    private ?string $region;
    private ?\M2E\TikTokShop\Model\Account $account;

    public function __construct(
        string $backUrl,
        ?string $region = null,
        ?\M2E\TikTokShop\Model\Account $account = null
    ) {
        $this->backUrl = $backUrl;
        $this->region = $region;
        $this->account = $account;
    }

    public function getCommand(): array
    {
        return ['account', 'get', 'grantAccessUrl'];
    }

    public function getRequestData(): array
    {
        $requestParams = [
            'back_url' => $this->backUrl,
        ];

        if (!empty($this->region)) {
            $requestParams['region'] = $this->region;
        }

        if ($this->account !== null) {
            $requestParams['account'] = $this->account->getServerHash();
        }

        return $requestParams;
    }

    public function parseResponse(\M2E\TikTokShop\Model\Connector\Response $response): object
    {
        return new GetGrantAccessUrl\Response($response->getResponseData()['url']);
    }
}
