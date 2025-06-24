<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Dashboard\Shipments;

class DataProvider implements \M2E\Core\Model\Dashboard\Shipments\DataProviderInterface
{
    use \M2E\TikTokShop\Model\Dashboard\CacheIntValueTrait;

    private const CACHE_LIFE_TIME = 600; // 10 min

    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;
    private \M2E\Core\Model\Dashboard\DateRangeFactory $dateRangeFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache,
        \M2E\Core\Model\Dashboard\DateRangeFactory $dateRangeFactory
    ) {
        $this->dateRangeFactory = $dateRangeFactory;
        $this->orderRepository = $orderRepository;
        $this->cache = $cache;
    }

    public function getCountOfLateShipments(): int
    {
        return $this->getCachedValue(__METHOD__, self::CACHE_LIFE_TIME, function () {
            return $this->orderRepository->getLateUnshippedCount();
        });
    }

    public function getCountOfShipByTomorrow(): int
    {
        return $this->getCachedValue(__METHOD__, self::CACHE_LIFE_TIME, function () {
            $range = $this->dateRangeFactory->createForTomorrow();

            return $this->orderRepository->getUnshippedCountForRange($range->dateStart, $range->dateEnd);
        });
    }

    public function getCountOfShipByToday(): int
    {
        return $this->getCachedValue(__METHOD__, self::CACHE_LIFE_TIME, function () {
            $from = \M2E\Core\Helper\Date::createCurrentGmt();
            $to = $this->dateRangeFactory->createForToday()->dateEnd;

            return $this->orderRepository->getUnshippedCountForRange($from, $to);
        });
    }

    public function getCountOfShipForTwoAndMoreDays(): int
    {
        return $this->getCachedValue(__METHOD__, self::CACHE_LIFE_TIME, function () {
            $from = $this->dateRangeFactory->createForTwoAndMoreDays()->dateStart;

            return $this->orderRepository->getUnshippedCountFrom($from);
        });
    }
}
