<?php

namespace M2E\TikTokShop\Model\Cron;

class Strategy extends \M2E\TikTokShop\Model\AbstractModel
{
    public const LOCK_ITEM_NICK = 'cron_strategy_serial';
    public const INITIALIZATION_TRANSACTIONAL_LOCK_NICK = 'cron_strategy_initialization';
    public const PROGRESS_START_EVENT_NAME = 'm2e_tts_cron_progress_start';
    public const PROGRESS_SET_PERCENTAGE_EVENT_NAME = 'm2e_tts_cron_progress_set_percentage';
    public const PROGRESS_SET_DETAILS_EVENT_NAME = 'm2e_tts_cron_progress_set_details';
    public const PROGRESS_STOP_EVENT_NAME = 'm2e_tts_cron_progress_stop';

    private TaskFactory $taskFactory;
    private \M2E\TikTokShop\Helper\Module\Cron $cronHelper;
    private \M2E\TikTokShop\Model\Cron\Strategy\Observer\KeepAlive $observerKeepAlive;
    private \M2E\TikTokShop\Model\Cron\Strategy\Observer\Progress $observerProgress;
    private \M2E\TikTokShop\Model\ActiveRecord\Factory $activeRecordFactory;
    private ?\M2E\TikTokShop\Model\Lock\Transactional\Manager $initializationLockManager = null;
    private \M2E\TikTokShop\Model\Cron\TaskRepository $taskRepo;
    private \M2E\TikTokShop\Model\Lock\Item\ManagerFactory $lockItemManagerFactory;
    private ?\M2E\TikTokShop\Model\Lock\Item\Manager $lockItemManager = null;
    private ?array $allowedTasks = null;
    private ?int $initiator = null;
    private ?OperationHistory $operationHistory = null;
    private ?OperationHistory $parentOperationHistory = null;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;
    private \M2E\TikTokShop\Model\Lock\Transactional\ManagerFactory $lockTransactionalManagerFactory;

    public function __construct(
        TaskFactory $taskFactory,
        \M2E\TikTokShop\Model\Lock\Transactional\ManagerFactory $lockTransactionalManagerFactory,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper,
        \M2E\TikTokShop\Model\Lock\Item\ManagerFactory $lockItemManagerFactory,
        \M2E\TikTokShop\Helper\Module\Cron $cronHelper,
        \M2E\TikTokShop\Model\Cron\Strategy\Observer\KeepAlive $observerKeepAlive,
        \M2E\TikTokShop\Model\Cron\Strategy\Observer\Progress $observerProgress,
        \M2E\TikTokShop\Model\ActiveRecord\Factory $activeRecordFactory,
        \M2E\TikTokShop\Model\Cron\TaskRepository $taskRepo
    ) {
        parent::__construct();

        $this->taskFactory = $taskFactory;
        $this->lockItemManagerFactory = $lockItemManagerFactory;
        $this->cronHelper = $cronHelper;
        $this->observerKeepAlive = $observerKeepAlive;
        $this->observerProgress = $observerProgress;
        $this->activeRecordFactory = $activeRecordFactory;
        $this->taskRepo = $taskRepo;
        $this->exceptionHelper = $exceptionHelper;
        $this->lockTransactionalManagerFactory = $lockTransactionalManagerFactory;
    }

    // ----------------------------------------

    public function setAllowedTasks(array $tasks): self
    {
        $this->allowedTasks = $tasks;

        return $this;
    }

    public function setInitiator(int $initiator): self
    {
        $this->initiator = $initiator;

        return $this;
    }

    public function setParentOperationHistory(\M2E\TikTokShop\Model\Cron\OperationHistory $operationHistory): self
    {
        $this->parentOperationHistory = $operationHistory;

        return $this;
    }

    // ----------------------------------------

    private function beforeStart(): void
    {
        $parentId = $this->getParentOperationHistory()
            ? $this->getParentOperationHistory()->getObject()->getId() : null;
        $this->getOperationHistory()->start('cron_strategy_serial', $parentId, $this->getInitiator());
        $this->getOperationHistory()->makeShutdownFunction();
    }

    public function process(): void
    {
        $this->beforeStart();

        try {
            $this->processTasks();
        } catch (\Throwable $exception) {
            $this->processException($exception);
        }

        $this->afterEnd();
    }

    private function afterEnd(): void
    {
        $this->getOperationHistory()->stop();
    }

    private function processTasks(): void
    {
        if ($this->getLockItemManager() === null) {
            return;
        }

        $this->getInitializationLockManager()->lock();

        try {
            $this->getLockItemManager()->create();

            $this->makeLockItemShutdownFunction($this->getLockItemManager());

            $this->getInitializationLockManager()->unlock();

            $this->keepAliveStart($this->getLockItemManager());
            $this->startListenProgressEvents($this->getLockItemManager());

            $this->processAllTasks();

            $this->keepAliveStop();
            $this->stopListenProgressEvents();
        } catch (\Throwable $exception) {
            $this->processException($exception);
        }

        $this->getLockItemManager()->remove();
    }

    private function processAllTasks(): void
    {
        $taskGroup = null;

        /**
         * Developer cron runner
         */
        if ($this->allowedTasks === null) {
            $taskGroup = $this->getNextTaskGroup();
            $this->cronHelper->setLastExecutedTaskGroup($taskGroup);
        }

        foreach ($this->getAllowedTasks($taskGroup) as $taskClassName) {
            try {
                $task = $this->taskFactory->createByClassName(
                    $taskClassName,
                    $this->getInitiator(),
                    $this->getOperationHistory(),
                    $this->getLockItemManager()
                );

                $task->process();
            } catch (\Throwable $exception) {
                $this->processException($exception);
            }
        }
    }

    private function getAllowedTasks($taskGroup): array
    {
        return $this->allowedTasks
            ?? $this->allowedTasks = $this->taskRepo->getGroupTasks($taskGroup);
    }

    private function getLockItemManager(): ?\M2E\TikTokShop\Model\Lock\Item\Manager
    {
        if ($this->lockItemManager !== null) {
            return $this->lockItemManager;
        }

        $lockItemManager = $this->lockItemManagerFactory->create(self::LOCK_ITEM_NICK);
        if (!$lockItemManager->isExist()) {
            return $this->lockItemManager = $lockItemManager;
        }

        if (
            $lockItemManager->isInactiveMoreThanSeconds(
                \M2E\TikTokShop\Model\Lock\Item\Manager::DEFAULT_MAX_INACTIVE_TIME
            )
        ) {
            $lockItemManager->remove();

            return $this->lockItemManager = $lockItemManager;
        }

        return null;
    }

    private function getInitiator(): ?int
    {
        return $this->initiator;
    }

    // ---------------------------------------

    /**
     * @return \M2E\TikTokShop\Model\Cron\OperationHistory
     */
    private function getParentOperationHistory(): ?OperationHistory
    {
        return $this->parentOperationHistory;
    }

    // ---------------------------------------

    private function getNextTaskGroup()
    {
        $lastExecuted = $this->cronHelper->getLastExecutedTaskGroup();
        $allowed = $this->taskRepo->getRegisteredGroups();
        $lastExecutedIndex = array_search($lastExecuted, $allowed, true);

        if (empty($lastExecuted) || $lastExecutedIndex === false || end($allowed) === $lastExecuted) {
            return reset($allowed);
        }

        return $allowed[$lastExecutedIndex + 1];
    }

    private function keepAliveStart(\M2E\TikTokShop\Model\Lock\Item\Manager $lockItemManager): void
    {
        $this->observerKeepAlive->enable();
        $this->observerKeepAlive->setLockItemManager($lockItemManager);
    }

    private function keepAliveStop(): void
    {
        $this->observerKeepAlive->disable();
    }

    private function startListenProgressEvents(\M2E\TikTokShop\Model\Lock\Item\Manager $lockItemManager): void
    {
        $this->observerProgress->enable();
        $this->observerProgress->setLockItemManager($lockItemManager);
    }

    private function stopListenProgressEvents(): void
    {
        $this->observerProgress->disable();
    }

    private function getOperationHistory(): \M2E\TikTokShop\Model\Cron\OperationHistory
    {
        if ($this->operationHistory !== null) {
            return $this->operationHistory;
        }

        return $this->operationHistory = $this->activeRecordFactory->getObject('Cron_OperationHistory');
    }

    private function makeLockItemShutdownFunction(\M2E\TikTokShop\Model\Lock\Item\Manager $lockItemManager): void
    {
        /** @var \M2E\TikTokShop\Model\Lock\Item $lockItem */
        $lockItem = $this->activeRecordFactory->getObjectLoaded('Lock\Item', $lockItemManager->getNick(), 'nick');
        if (!$lockItem->getId()) {
            return;
        }

        $id = $lockItem->getId();

        // @codingStandardsIgnoreLine
        register_shutdown_function(
            function () use ($id) {
                $error = error_get_last();
                if ($error === null || !in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
                    return;
                }

                /** @var \M2E\TikTokShop\Model\Lock\Item $lockItem */
                $lockItem = $this->activeRecordFactory->getObjectLoaded('Lock_Item', $id);
                if ($lockItem->getId()) {
                    $lockItem->delete();
                }
            }
        );
    }

    private function getInitializationLockManager(): \M2E\TikTokShop\Model\Lock\Transactional\Manager
    {
        if ($this->initializationLockManager !== null) {
            return $this->initializationLockManager;
        }

        $lockTransactionalManager = $this->lockTransactionalManagerFactory->create(
            self::INITIALIZATION_TRANSACTIONAL_LOCK_NICK,
        );

        return $this->initializationLockManager = $lockTransactionalManager;
    }

    private function processException(\Throwable $exception): void
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

        $this->exceptionHelper->process($exception);
    }
}
