<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Account;

class AddCommand implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    private string $authCode;
    private string $region;

    public function __construct(string $authCode, string $region)
    {
        $this->authCode = $authCode;
        $this->region = $region;
    }

    public function getCommand(): array
    {
        return ['account', 'add', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'auth_code' => $this->authCode,
            'region' => $this->region,
        ];
    }

    public function parseResponse(\M2E\TikTokShop\Model\Connector\Response $response): Add\Response
    {
        $responseData = $response->getResponseData();

        $shops = [];
        foreach ($responseData['shops'] as $shopData) {
            $shops[] = new \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Shop(
                $shopData['shop_id'],
                $shopData['shop_name'],
                $shopData['region'],
                $shopData['type']
            );
        }

        return new \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Add\Response(
            $responseData['hash'],
            $responseData['account']['open_id'],
            $responseData['account']['name'],
            $responseData['account']['region'],
            $shops
        );
    }
}
