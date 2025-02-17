<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Shop;

class GetShopStatus implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $shopId;
    private string $accountHash;

    public function __construct(string $accountHash, string $shopId)
    {
        $this->accountHash = $accountHash;
        $this->shopId = $shopId;
    }

    public function getCommand(): array
    {
        return ['shop', 'get', 'listingPrerequisites'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'shop_id' => $this->shopId,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): object {
        $data = $response->getResponseData();

        $issues = [];
        foreach ($data['issues'] as $issueData) {
            $issues[] = new \M2E\TikTokShop\Model\TikTokShop\Connector\Shop\Issue(
                $issueData['type'],
                $issueData['message']
            );
        }

        return new \M2E\TikTokShop\Model\TikTokShop\Connector\Shop\Get\Response(
            $issues
        );
    }
}
