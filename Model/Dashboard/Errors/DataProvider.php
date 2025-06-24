<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Dashboard\Errors;

class DataProvider implements \M2E\Core\Model\Dashboard\Errors\DataProviderInterface
{
    use \M2E\TikTokShop\Model\Dashboard\CacheIntValueTrait;

    private const CACHE_LIFE_TIME = 600; // 10 min

    private \M2E\TikTokShop\Model\Listing\Log\Repository $logRepository;
    private \M2E\Core\Model\Dashboard\DateRangeFactory $dateRangeFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Log\Repository $logRepository,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache,
        \M2E\Core\Model\Dashboard\DateRangeFactory $dateRangeFactory
    ) {
        $this->dateRangeFactory = $dateRangeFactory;
        $this->logRepository = $logRepository;
        $this->cache = $cache;
    }

    public function getCountErrorsForToday(): int
    {
        return $this->getCachedValue(__METHOD__, self::CACHE_LIFE_TIME, function () {
            $range = $this->dateRangeFactory->createForToday();

            return $this->logRepository->getCountErrorsByDateRange($range->dateStart, $range->dateEnd);
        });
    }

    public function getCountErrorsForYesterday(): int
    {
        return $this->getCachedValue(__METHOD__, self::CACHE_LIFE_TIME, function () {
            $range = $this->dateRangeFactory->createForYesterday();

            return $this->logRepository->getCountErrorsByDateRange($range->dateStart, $range->dateEnd);
        });
    }

    public function getCountErrorsFor2DaysAgo(): int
    {
        return $this->getCachedValue(__METHOD__, self::CACHE_LIFE_TIME, function () {
            $range = $this->dateRangeFactory->createFor2DaysAgo();

            return $this->logRepository->getCountErrorsByDateRange($range->dateStart, $range->dateEnd);
        });
    }

    public function getTotalErrorsCount(): int
    {
        return $this->getCachedValue(__METHOD__, self::CACHE_LIFE_TIME, function () {
            return $this->logRepository->getCountErrorsByDateRange();
        });
    }
}
