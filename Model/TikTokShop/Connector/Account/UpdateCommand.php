<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Account;

class UpdateCommand implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $authCode;

    public function __construct(string $accountHash, string $authCode)
    {
        $this->accountHash = $accountHash;
        $this->authCode = $authCode;
    }

    public function getCommand(): array
    {
        return ['account', 'update', 'entity'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'auth_code' => $this->authCode,
        ];
    }

    public function parseResponse(
        \M2E\TikTokShop\Model\Connector\Response $response
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Update\Response {
        $responseData = $response->getResponseData();

        $sellerName = $responseData['account']['name'];

        $shops = [];
        foreach ($responseData['shops'] as $shopData) {
            $shops[] = new \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Shop(
                $shopData['shop_id'],
                $shopData['shop_name'],
                $shopData['region'],
                $shopData['type']
            );
        }

        return new \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Update\Response(
            $shops,
            $sellerName
        );
    }
}
