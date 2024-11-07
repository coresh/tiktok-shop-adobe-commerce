<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Brands\Get;

class Processor
{
    private const PAGE_LIMIT = 5;

    private \M2E\TikTokShop\Model\Connector\Client\Single $singleClient;

    public function __construct(
        \M2E\TikTokShop\Model\Connector\Client\Single $singleClient
    ) {
        $this->singleClient = $singleClient;
    }

    public function processAuthorizedBrands(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        string $categoryId = ''
    ) {
        return $this->process($account, $shop, $categoryId, '', true);
    }

    private function process(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        string $categoryId = '',
        string $brandName = '',
        ?bool $isAuthorized = null
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Brands\Get\Response {
        $accountHash = $account->getServerHash();
        $shopId = $shop->getShopId();

        $allBrands = [];
        $nextPageToken = null;
        $currentPage = 0;

        do {
            $currentPage++;

            $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Brands\GetCommand(
                $accountHash,
                $shopId,
                $categoryId,
                $brandName,
                $isAuthorized,
                $nextPageToken
            );

            /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Brands\Get\Response $response */
            $response = $this->singleClient->process($command);
            $allBrands = array_merge($allBrands, $response->getBrands());
            $nextPageToken = $response->getNextPageToken();
        } while ($nextPageToken !== null && self::PAGE_LIMIT > $currentPage);

        return new \M2E\TikTokShop\Model\TikTokShop\Connector\Brands\Get\Response(
            $allBrands,
            $response->getTotalCount(),
            null
        );
    }
}
