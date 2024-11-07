<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Log;

class Clearing
{
    public const LOG_LISTINGS = 'listings';
    public const LOG_SYNCHRONIZATIONS = 'synchronizations';
    public const LOG_ORDERS = 'orders';

    private \M2E\TikTokShop\Model\Config\Manager $config;
    private \M2E\TikTokShop\Model\Listing\Log\Repository $listingLogRepository;
    private \M2E\TikTokShop\Model\Synchronization\Log\Repository $syncLogRepository;
    private \M2E\TikTokShop\Model\Order\Log\Repository $orderLogRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Config\Manager $config,
        \M2E\TikTokShop\Model\Listing\Log\Repository $listingLogRepository,
        \M2E\TikTokShop\Model\Synchronization\Log\Repository $syncLogRepository,
        \M2E\TikTokShop\Model\Order\Log\Repository $orderLogRepository
    ) {
        $this->config = $config;
        $this->listingLogRepository = $listingLogRepository;
        $this->syncLogRepository = $syncLogRepository;
        $this->orderLogRepository = $orderLogRepository;
    }

    public function saveSettings(string $log, bool $mode, int $days): void
    {
        if (!$this->isValidLogType($log)) {
            return;
        }

        if ($days <= 0) {
            $days = 90;
        }

        $this->config->setGroupValue('/logs/clearing/' . $log . '/', 'mode', (int)$mode);
        $this->config->setGroupValue('/logs/clearing/' . $log . '/', 'days', $days);
    }

    // ----------------------------------------

    public function clearOldRecords(string $logType): void
    {
        if (!$this->isValidLogType($logType)) {
            return;
        }

        $mode = (int)$this->config->getGroupValue('/logs/clearing/' . $logType . '/', 'mode');
        $days = (int)$this->config->getGroupValue('/logs/clearing/' . $logType . '/', 'days');

        if ($mode !== 1 || $days <= 0) {
            return;
        }

        $minTime = $this->getMinTimeByDays($days);
        $this->clearLogByMinTime($logType, $minTime);
    }

    public function clearAllLog(string $logType): void
    {
        if (!$this->isValidLogType($logType)) {
            return;
        }

        $this->clearLogByMinTime($logType, null);
    }

    private function isValidLogType(string $logType): bool
    {
        return $logType === self::LOG_LISTINGS
            || $logType === self::LOG_SYNCHRONIZATIONS
            || $logType === self::LOG_ORDERS;
    }

    private function getMinTimeByDays(int $days): \DateTime
    {
        $date = \M2E\TikTokShop\Helper\Date::createCurrentGmt();
        $date->modify('- ' . $days . ' days');

        return $date;
    }

    private function clearLogByMinTime(string $logType, ?\DateTime $borderDate): void
    {
        switch ($logType) {
            case self::LOG_LISTINGS:
                $this->listingLogRepository->remove($borderDate);
                break;
            case self::LOG_SYNCHRONIZATIONS:
                $this->syncLogRepository->remove($borderDate);
                break;
            case self::LOG_ORDERS:
                $this->orderLogRepository->remove($borderDate);
                break;
        }
    }
}
