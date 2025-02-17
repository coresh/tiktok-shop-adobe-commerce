<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron;

class TaskProcessor
{
    private int $initiator;
    /** @var \M2E\TikTokShop\Model\Cron\OperationHistory */
    private OperationHistory $operationHistory;
    /** @var \M2E\TikTokShop\Model\Cron\OperationHistory */
    private OperationHistory $parentOperationHistory;
    /** @var \M2E\TikTokShop\Model\Cron\Manager */
    private Manager $cronManager;
    /** @var \M2E\TikTokShop\Model\Cron\Config */
    private Config $cronConfig;
    private \Magento\Framework\Event\Manager $eventManager;
    private \M2E\Core\Model\Cron\TaskDefinition $taskDefinition;
    private \M2E\Core\Model\Cron\TaskHandlerFactory $taskHandlerFactory;
    private \M2E\TikTokShop\Model\Synchronization\LogService $syncLogger;
    /** @var \M2E\TikTokShop\Model\Cron\OperationHistoryFactory */
    private OperationHistoryFactory $operationHistoryFactory;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        \M2E\Core\Model\Cron\TaskDefinition $taskDefinition,
        int $initiator,
        \M2E\TikTokShop\Model\Synchronization\LogService $syncLogger,
        \M2E\TikTokShop\Model\Cron\OperationHistory $parentOperationHistory,
        Manager $cronManager,
        Config $cronConfig,
        \Magento\Framework\Event\Manager $eventManager,
        \M2E\Core\Model\Cron\TaskHandlerFactory $taskHandlerFactory,
        \M2E\TikTokShop\Model\Cron\OperationHistoryFactory $operationHistoryFactory,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper
    ) {
        $this->initiator = $initiator;
        $this->parentOperationHistory = $parentOperationHistory;
        $this->cronManager = $cronManager;
        $this->cronConfig = $cronConfig;
        $this->eventManager = $eventManager;
        $this->taskDefinition = $taskDefinition;
        $this->taskHandlerFactory = $taskHandlerFactory;
        $this->syncLogger = $syncLogger;
        $this->operationHistoryFactory = $operationHistoryFactory;
        $this->exceptionHelper = $exceptionHelper;
    }

    public function process(): void
    {
        $this->initialize();

        $this->cronManager->setTaskLastAccess($this->taskDefinition->getNick());

        // ----------------------------------------

        if (!$this->isPossibleToRun()) {
            return;
        }

        $taskHandler = $this->taskHandlerFactory->create($this->taskDefinition);
        if (
            $taskHandler instanceof \M2E\Core\Model\Cron\Task\PossibleRunInterface
            && !$taskHandler->isPossibleToRun()
        ) {
            return;
        }

        // ----------------------------------------

        $this->cronManager->setTaskLastRun($this->taskDefinition->getNick());
        $this->beforeStart();

        try {
            $this->eventManager->dispatch(
                \M2E\TikTokShop\Model\Cron\Strategy::PROGRESS_START_EVENT_NAME,
                ['progress_nick' => $this->taskDefinition->getNick()]
            );

            // ----------------------------------------

            $context = $this->createContext();

            $taskHandler->process($context);

            // ----------------------------------------

            $this->eventManager->dispatch(
                \M2E\TikTokShop\Model\Cron\Strategy::PROGRESS_STOP_EVENT_NAME,
                ['progress_nick' => $this->taskDefinition->getNick()]
            );
        } catch (\Throwable $exception) {
            $this->processTaskException($exception);
        } finally {
            $this->afterEnd();
        }
    }

    // ----------------------------------------

    private function initialize(): void
    {
        $this->syncLogger->setInitiator($this->initiator);

        $this->exceptionHelper->setFatalErrorHandler();
        $this->syncLogger->registerFatalErrorHandler();
    }

    // ----------------------------------------

    private function isPossibleToRun(): bool
    {
        if ($this->initiator === \M2E\TikTokShop\Helper\Data::INITIATOR_DEVELOPER) {
            return true;
        }

        if (!$this->cronConfig->isTaskEnabled($this->taskDefinition->getNick())) {
            return false;
        }

        return $this->isIntervalExceeded();
    }

    private function isIntervalExceeded(): bool
    {
        $lastRunDateTime = $this->cronManager->getTaskLastRun($this->taskDefinition->getNick());
        if ($lastRunDateTime === null) {
            return true;
        }

        $currentTimestamp = \M2E\Core\Helper\Date::createCurrentGmt()->getTimestamp();

        return $currentTimestamp > ($lastRunDateTime->getTimestamp() + $this->getIntervalInSeconds());
    }

    private function getIntervalInSeconds(): int
    {
        $interval = $this->cronConfig->getTaskConfiguredIntervalInSeconds($this->taskDefinition->getNick());
        if ($interval !== null) {
            return $interval;
        }

        return $this->taskDefinition->getIntervalInSeconds();
    }

    // ----------------------------------------

    private function createContext(): TaskContext
    {
        return new TaskContext(
            $this->initiator,
            $this->syncLogger,
            $this->getOperationHistory(),
            new \M2E\TikTokShop\Model\Cron\ExceptionHandler(
                $this->getOperationHistory(),
                $this->syncLogger,
                $this->exceptionHelper
            )
        );
    }

    // ----------------------------------------

    private function beforeStart(): void
    {
        $parentId = $this->parentOperationHistory->getObject() !== null
            ? (int)$this->parentOperationHistory->getObject()->getId() : null;

        $nick = str_replace('/', '_', $this->taskDefinition->getNick());
        $this->getOperationHistory()->start('cron_task_' . $nick, $parentId, $this->initiator);

        $this->getOperationHistory()->makeShutdownFunction();

        $this->syncLogger->setOperationHistoryId(
            $this->getOperationHistory()->getObject()->getId() // not null because was start
        );
    }

    private function afterEnd(): void
    {
        $this->getOperationHistory()->stop();
    }

    // ----------------------------------------

    private function getOperationHistory(): \M2E\TikTokShop\Model\Cron\OperationHistory
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->operationHistory)) {
            return $this->operationHistory;
        }

        return $this->operationHistory = $this->operationHistoryFactory->create();
    }

    private function processTaskException(\Throwable $exception): void
    {
        $this->getOperationHistory()->addContentData(
            'exceptions',
            [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]
        );

        $this->syncLogger->addFromException($exception);

        $this->exceptionHelper->process($exception);
    }
}
