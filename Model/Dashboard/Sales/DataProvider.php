<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Dashboard\Sales;

class DataProvider implements \M2E\Core\Model\Dashboard\Sales\DataProviderInterface
{
    private \M2E\TikTokShop\Model\Dashboard\Sales\Cache $cache;
    private \M2E\Core\Model\Dashboard\DateRangeFactory $dateRangeFactory;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Dashboard\Sales\Cache $cache,
        \M2E\Core\Model\Dashboard\DateRangeFactory $dateRangeFactory,
        \M2E\TikTokShop\Model\Order\Repository $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->dateRangeFactory = $dateRangeFactory;
        $this->cache = $cache;
    }

    public function getAmountPointsFor24Hours(): array
    {
        return $this->cache->getCachedPoints(__METHOD__, function () {
            $dateRange = $this->dateRangeFactory->createForLast24Hours();

            return $this->orderRepository->getAmountPoints(
                $dateRange->dateStart,
                $dateRange->dateEnd,
                true
            );
        });
    }

    public function getQtyPointsFor24Hours(): array
    {
        return $this->cache->getCachedPoints(__METHOD__, function () {
            $dateRange = $this->dateRangeFactory->createForLast24Hours();

            return $this->orderRepository->getQuantityPoints(
                $dateRange->dateStart,
                $dateRange->dateEnd,
                true
            );
        });
    }

    public function getAmountPointsFor7Days(): array
    {
        return $this->cache->getCachedPoints(__METHOD__, function () {
            $dateRange = $this->dateRangeFactory->createForLast7Days();

            return $this->orderRepository->getAmountPoints(
                $dateRange->dateStart,
                $dateRange->dateEnd,
                false
            );
        });
    }

    public function getQtyPointsFor7Days(): array
    {
        return $this->cache->getCachedPoints(__METHOD__, function () {
            $dateRange = $this->dateRangeFactory->createForLast7Days();

            return $this->orderRepository->getQuantityPoints(
                $dateRange->dateStart,
                $dateRange->dateEnd,
                false
            );
        });
    }

    public function getAmountPointsForToday(): array
    {
        return $this->cache->getCachedPoints(__METHOD__, function () {
            $dateRange = $this->dateRangeFactory->createForToday();

            return $this->orderRepository->getAmountPoints(
                $dateRange->dateStart,
                $dateRange->dateEnd,
                true
            );
        });
    }

    public function getQtyPointsForToday(): array
    {
        return $this->cache->getCachedPoints(__METHOD__, function () {
            $dateRange = $this->dateRangeFactory->createForToday();

            return $this->orderRepository->getQuantityPoints(
                $dateRange->dateStart,
                $dateRange->dateEnd,
                true
            );
        });
    }
}
