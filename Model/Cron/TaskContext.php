<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron;

class TaskContext
{
    private int $initiator;
    private \M2E\TikTokShop\Model\Synchronization\LogService $synchronizationLog;
    /** @var \M2E\TikTokShop\Model\Cron\OperationHistory */
    private OperationHistory $operationHistory;
    /** @var \M2E\TikTokShop\Model\Cron\ExceptionHandler */
    private ExceptionHandler $exceptionHandler;

    public function __construct(
        int $initiator,
        \M2E\TikTokShop\Model\Synchronization\LogService $synchronizationLog,
        \M2E\TikTokShop\Model\Cron\OperationHistory $operationHistory,
        \M2E\TikTokShop\Model\Cron\ExceptionHandler $exceptionHandler
    ) {
        $this->initiator          = $initiator;
        $this->synchronizationLog = $synchronizationLog;
        $this->operationHistory   = $operationHistory;
        $this->exceptionHandler   = $exceptionHandler;
    }

    public function getInitiator(): int
    {
        return $this->initiator;
    }

    public function getSynchronizationLog(): \M2E\TikTokShop\Model\Synchronization\LogService
    {
        return $this->synchronizationLog;
    }

    public function getOperationHistory(): OperationHistory
    {
        return $this->operationHistory;
    }

    public function getExceptionHandler(): ExceptionHandler
    {
        return $this->exceptionHandler;
    }
}
