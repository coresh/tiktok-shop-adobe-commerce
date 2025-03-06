<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\RecommendedCategory;

class GetCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $shopId;
    private string $productTitle;

    public function __construct(
        string $accountHash,
        string $shopId,
        string $productTitle
    ) {
        $this->accountHash = $accountHash;
        $this->shopId = $shopId;
        $this->productTitle = $productTitle;
    }

    public function getCommand(): array
    {
        return ['category', 'get', 'recommended'];
    }

    public function getRequestData(): array
    {
        $requestParams = [
            'account' => $this->accountHash,
            'shop_id' => $this->shopId,
            'is_global' => true,
            'product' => [
                'title' => $this->productTitle
            ]
        ];

        return $requestParams;
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): object
    {
        $responseData = $response->getResponseData();

        return new \M2E\TikTokShop\Model\TikTokShop\Connector\RecommendedCategory\Get\Response(
            $responseData['category']['id']
        );
    }
}
