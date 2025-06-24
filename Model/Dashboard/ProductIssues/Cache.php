<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Dashboard\ProductIssues;

use M2E\Core\Model\Dashboard\ProductIssues\Issue;

class Cache
{
    private const CACHE_LIFE_TIME = 600; // 10 min

    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache;

    public function __construct(\M2E\TikTokShop\Helper\Data\Cache\Permanent $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return Issue[]
     */
    public function getCachedIssues(string $key, callable $handler): array
    {
        $issues = $this->getIssues($key);
        if ($issues !== null) {
            return $issues;
        }

        return $this->setIssues($key, $handler());
    }

    /**
     * @return Issue[]|null
     */
    private function getIssues(string $key): ?array
    {
        /** @var list<array{tag_id:int, text:string, total:int, impact_rate:int|float}> $value */
        $value = $this->cache->getValue($key);

        if ($value === null) {
            return null;
        }

        return array_map(
            static fn(array $item): Issue => Issue::createFromArray($item),
            $value
        );
    }

    private function setIssues(string $key, array $issues): array
    {
        $value = array_map(
            static fn(Issue $issue): array => $issue->toArray(),
            $issues
        );

        $this->cache->setValue($key, $value, [], self::CACHE_LIFE_TIME);

        return $issues;
    }
}
