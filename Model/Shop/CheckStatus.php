<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Shop;

class CheckStatus
{
    private const CACHE_LIFETIME = 300;

    private \M2E\TikTokShop\Model\Connector\Client\Single $singleClient;
    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\TikTokShop\Model\Connector\Client\Single $singleClient,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache
    ) {
        $this->singleClient = $singleClient;
        $this->cache = $cache;
    }

    /**
     * @param \M2E\TikTokShop\Model\Listing $listing
     *
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Shop\Issue[]
     */
    public function getIssues(\M2E\TikTokShop\Model\Listing $listing): array
    {
        $shop = $listing->getShop();

        $issues = $this->getCachedIssues($shop);
        if ($issues === null) {
            $issues = $this->retrieveIssues($listing, $shop);

            $this->cacheIssues($shop, $issues);
        }

        return $issues;
    }

    private function getCachedIssues(\M2E\TikTokShop\Model\Shop $shop): ?array
    {
        $data = $this->cache->getValue($this->createCacheKey($shop));
        if ($data === null) {
            return null;
        }

        return array_map(static function (array $raw) {
            return new \M2E\TikTokShop\Model\TikTokShop\Connector\Shop\Issue($raw['type'], $raw['message']);
        }, $data);
    }

    /**
     * @param \M2E\TikTokShop\Model\Shop $shop
     * @param \M2E\TikTokShop\Model\TikTokShop\Connector\Shop\Issue[] $issues
     *
     * @return void
     */
    private function cacheIssues(\M2E\TikTokShop\Model\Shop $shop, array $issues): void
    {
        $data = array_map(static function (\M2E\TikTokShop\Model\TikTokShop\Connector\Shop\Issue $issue) {
            return [
                'type' => $issue->getType(),
                'message' => $issue->getMessage(),
            ];
        }, $issues);

        $this->cache->setValue($this->createCacheKey($shop), $data, [], self::CACHE_LIFETIME);
    }

    private function createCacheKey(\M2E\TikTokShop\Model\Shop $shop): string
    {
        return sprintf('%s.%s.issues', $shop->getShopNameWithRegion(), $shop->getShopId());
    }

    /**
     * @param \M2E\TikTokShop\Model\Listing $listing
     * @param \M2E\TikTokShop\Model\Shop $shop
     *
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Shop\Issue[]
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     */
    private function retrieveIssues(\M2E\TikTokShop\Model\Listing $listing, \M2E\TikTokShop\Model\Shop $shop): array
    {
        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Shop\GetShopStatus(
            $listing->getAccount()->getServerHash(),
            $shop->getShopId(),
        );

        /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Shop\Get\Response $response */
        $response = $this->singleClient->process($command);

        return $response->getIssues();
    }
}
