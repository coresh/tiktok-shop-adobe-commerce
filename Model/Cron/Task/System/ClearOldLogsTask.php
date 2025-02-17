<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task\System;

class ClearOldLogsTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'system/clear_old_logs';

    private const SYSTEM_LOG_MAX_DAYS = 30;
    private const SYSTEM_LOG_MAX_RECORDS = 100000;
    private const OPERATION_HISTORY_MAX_DAYS = 5;

    private \M2E\TikTokShop\Model\Log\Clearing $clearing;
    private \M2E\TikTokShop\Model\OperationHistory\Repository $operationHistoryRepository;
    private \M2E\TikTokShop\Model\Log\System\Repository $systemLogRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Log\Clearing $clearing,
        \M2E\TikTokShop\Model\Log\System\Repository $systemLogRepository,
        \M2E\TikTokShop\Model\OperationHistory\Repository $operationHistoryRepository
    ) {
        $this->clearing = $clearing;
        $this->operationHistoryRepository = $operationHistoryRepository;
        $this->systemLogRepository = $systemLogRepository;
    }

    public function process($context): void
    {
        $this->clearDomainLogs();

        $this->clearSystemLog();
    }

    private function clearDomainLogs(): void
    {
        $this->clearing->clearOldRecords(\M2E\TikTokShop\Model\Log\Clearing::LOG_LISTINGS);
        $this->clearing->clearOldRecords(\M2E\TikTokShop\Model\Log\Clearing::LOG_SYNCHRONIZATIONS);
        $this->clearing->clearOldRecords(\M2E\TikTokShop\Model\Log\Clearing::LOG_ORDERS);

        $minDate = \M2E\Core\Helper\Date::createCurrentGmt()
                                              ->modify('-' . self::OPERATION_HISTORY_MAX_DAYS . ' days');
        $this->operationHistoryRepository->clear($minDate);
    }

    private function clearSystemLog(): void
    {
        $this->systemLogRepository->clearByAmount(self::SYSTEM_LOG_MAX_RECORDS);

        $minDate = \M2E\Core\Helper\Date::createCurrentGmt()
                                              ->modify('-' . self::SYSTEM_LOG_MAX_DAYS . ' days');
        $this->systemLogRepository->clearByTime($minDate);
    }
}
