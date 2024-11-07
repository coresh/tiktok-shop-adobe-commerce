<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task\System;

class ClearOldLogs extends \M2E\TikTokShop\Model\Cron\AbstractTask
{
    public const NICK = 'system/clear_old_logs';

    private const SYSTEM_LOG_MAX_DAYS = 30;
    private const SYSTEM_LOG_MAX_RECORDS = 100000;
    private const OPERATION_HISTORY_MAX_DAYS = 5;

    /**
     * @var int (in seconds)
     */
    protected $interval = 86400;

    private \M2E\TikTokShop\Model\Log\Clearing $clearing;
    private \M2E\TikTokShop\Model\OperationHistory\Repository $operationHistoryRepository;
    private \M2E\TikTokShop\Model\Log\System\Repository $systemLogRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Log\Clearing $clearing,
        \M2E\TikTokShop\Model\Log\System\Repository $systemLogRepository,
        \M2E\TikTokShop\Model\OperationHistory\Repository $operationHistoryRepository,
        \M2E\TikTokShop\Model\Cron\Manager $cronManager,
        \M2E\TikTokShop\Model\Synchronization\LogService $syncLogger,
        \M2E\TikTokShop\Helper\Data $helperData,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\TikTokShop\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\TikTokShop\Helper\Factory $helperFactory,
        \M2E\TikTokShop\Model\Cron\TaskRepository $taskRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $cronManager,
            $syncLogger,
            $helperData,
            $eventManager,
            $activeRecordFactory,
            $helperFactory,
            $taskRepo,
            $resource,
        );
        $this->clearing = $clearing;
        $this->operationHistoryRepository = $operationHistoryRepository;
        $this->systemLogRepository = $systemLogRepository;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $this->clearDomainLogs();

        $this->clearSystemLog();
    }

    private function clearDomainLogs(): void
    {
        $this->clearing->clearOldRecords(\M2E\TikTokShop\Model\Log\Clearing::LOG_LISTINGS);
        $this->clearing->clearOldRecords(\M2E\TikTokShop\Model\Log\Clearing::LOG_SYNCHRONIZATIONS);
        $this->clearing->clearOldRecords(\M2E\TikTokShop\Model\Log\Clearing::LOG_ORDERS);

        $minDate = \M2E\TikTokShop\Helper\Date::createCurrentGmt()
                                              ->modify('-' . self::OPERATION_HISTORY_MAX_DAYS . ' days');
        $this->operationHistoryRepository->clear($minDate);
    }

    private function clearSystemLog(): void
    {
        $this->systemLogRepository->clearByAmount(self::SYSTEM_LOG_MAX_RECORDS);

        $minDate = \M2E\TikTokShop\Helper\Date::createCurrentGmt()
                                              ->modify('-' . self::SYSTEM_LOG_MAX_DAYS . ' days');
        $this->systemLogRepository->clearByTime($minDate);
    }
}
