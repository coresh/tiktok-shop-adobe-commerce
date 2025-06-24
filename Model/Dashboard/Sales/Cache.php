<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Dashboard\Sales;

use M2E\Core\Model\Dashboard\Sales\Point;

class Cache
{
    private const CACHE_LIFE_TIME = 600; // 10 min

    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache;

    public function __construct(\M2E\TikTokShop\Helper\Data\Cache\Permanent $cache)
    {
        $this->cache = $cache;
    }

    public function getCachedPoints(string $key, callable $handler): array
    {
        $points = $this->getPoints($key);
        if ($points !== null) {
            return $points;
        }

        return $this->setPoints($key, $handler());
    }

    /**
     * @return Point[]|null
     */
    private function getPoints(string $key): ?array
    {
        /** @var array<array{value:float, date:string}>|null $value */
        $value = $this->cache->getValue($key);
        if ($value === null) {
            return null;
        }

        return array_map(
            static fn(array $item): Point => Point::createFromArray($item),
            $value
        );
    }

    private function setPoints(string $key, array $points): array
    {
        $value = array_map(
            static fn(Point $point): array => $point->toArray(),
            $points
        );

        $this->cache->setValue($key, $value, [], self::CACHE_LIFE_TIME);

        return $points;
    }
}
