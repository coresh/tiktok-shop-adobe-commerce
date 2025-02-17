<?php

namespace M2E\TikTokShop\Model\Cron;

class Strategy
{
    private const LOCK_MAX_INACTIVE_SECONDS = 900;
    public const LOCK_ITEM_NICK = 'cron_strategy_serial';
    public const INITIALIZATION_TRANSACTIONAL_LOCK_NICK = 'cron_strategy_initialization';

    public const PROGRESS_START_EVENT_NAME = 'm2e_tts_cron_progress_start';
    public const PROGRESS_SET_PERCENTAGE_EVENT_NAME = 'm2e_tts_cron_progress_set_percentage';
    public const PROGRESS_SET_DETAILS_EVENT_NAME = 'm2e_tts_cron_progress_set_details';
    public const PROGRESS_STOP_EVENT_NAME = 'm2e_tts_cron_progress_stop';

    // ----------------------------------------

    private bool $initialized = false;
    private int $initiator;
    private OperationHistory $parentOperationHistory;

    // ----------------------------------------

    /** @var string[] */
    private ?array $allowedTasks = null;
    private \M2E\TikTokShop\Model\Cron\Strategy\Observer\KeepAlive $observerKeepAlive;
    private \M2E\TikTokShop\Model\Cron\Strategy\Observer\Progress $observerProgress;
    private \M2E\TikTokShop\Model\Lock\Transactional\Manager $initializationLockManager;
    private \M2E\TikTokShop\Model\Cron\TaskCollection $taskCollection;
    private \M2E\TikTokShop\Model\Lock\Item\ManagerFactory $lockItemManagerFactory;
    private \M2E\TikTokShop\Model\Lock\Item\Manager $lockItemManager;
    private OperationHistory $operationHistory;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;
    private \M2E\TikTokShop\Model\Lock\Transactional\ManagerFactory $lockTransactionalManagerFactory;
    private \M2E\TikTokShop\Model\Lock\Item\Repository $lockItemRepository;
    /** @var \M2E\TikTokShop\Model\Cron\OperationHistoryFactory */
    private OperationHistoryFactory $operationHistoryFactory;
    /** @var \M2E\TikTokShop\Model\Cron\Manager */
    private Manager $cronManager;
    /** @var \M2E\TikTokShop\Model\Cron\TaskProcessorFactory */
    private TaskProcessorFactory $taskProcessorFactory;

    public function __construct(
        Manager $cronManager,
        \M2E\TikTokShop\Model\Cron\TaskProcessorFactory $taskProcessorFactory,
        \M2E\TikTokShop\Model\Lock\Transactional\ManagerFactory $lockTransactionalManagerFactory,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper,
        \M2E\TikTokShop\Model\Lock\Item\ManagerFactory $lockItemManagerFactory,
        \M2E\TikTokShop\Model\Cron\Strategy\Observer\KeepAlive $observerKeepAlive,
        \M2E\TikTokShop\Model\Cron\Strategy\Observer\Progress $observerProgress,
        \M2E\TikTokShop\Model\Cron\TaskCollection $taskRepo,
        \M2E\TikTokShop\Model\Lock\Item\Repository $lockItemRepository,
        \M2E\TikTokShop\Model\Cron\OperationHistoryFactory $operationHistoryFactory
    ) {
        $this->lockItemManagerFactory = $lockItemManagerFactory;
        $this->observerKeepAlive = $observerKeepAlive;
        $this->observerProgress = $observerProgress;
        $this->taskCollection = $taskRepo;
        $this->exceptionHelper = $exceptionHelper;
        $this->lockTransactionalManagerFactory = $lockTransactionalManagerFactory;
        $this->lockItemRepository = $lockItemRepository;
        $this->operationHistoryFactory = $operationHistoryFactory;
        $this->cronManager = $cronManager;
        $this->taskProcessorFactory = $taskProcessorFactory;
    }

    // ----------------------------------------

    public function initialize(
        int $initiator,
        \M2E\TikTokShop\Model\Cron\OperationHistory $parentOperationHistory
    ): void {
        $this->initiator = $initiator;
        $this->parentOperationHistory = $parentOperationHistory;

        $this->initialized = true;
    }

    /**
     * @param string[] $tasksNicks
     *
     * @return $this
     */
    public function setAllowedTasks(array $tasksNicks): self
    {
        $this->allowedTasks = $tasksNicks;

        return $this;
    }

    private function getInitiator(): int
    {
        return $this->initiator;
    }

    // ----------------------------------------

    public function process(): void
    {
        if (!$this->initialized) {
            throw new \LogicException('Can not process the strategy without being initialized.');
        }

        $this->beforeStart();

        try {
            $this->processTasks();
        } catch (\Throwable $exception) {
            $this->processException($exception);
        } finally {
            $this->afterEnd();
        }
    }

    private function beforeStart(): void
    {
        $parentId = $this->parentOperationHistory->getObject() !== null
            ? (int)$this->parentOperationHistory->getObject()->getId() : null;

        $this->getOperationHistory()->start('cron_strategy_serial', $parentId, $this->getInitiator());

        $this->getOperationHistory()->makeShutdownFunction();
    }

    private function processTasks(): void
    {
        if (!$this->tryRetrieveLockManager()) {
            return;
        }

        $this->getInitializationLockManager()->lock();

        try {
            $this->getLockItemManager()->create();

            $this->makeLockItemShutdownFunction($this->getLockItemManager());

            $this->getInitializationLockManager()->unlock();

            // ----------------------------------------

            $this->keepAliveStart($this->getLockItemManager());
            $this->startListenProgressEvents($this->getLockItemManager());

            // ----------------------------------------

            $this->processAllTasks();

            // ----------------------------------------

            $this->keepAliveStop();
            $this->stopListenProgressEvents();
        } catch (\Throwable $exception) {
            $this->processException($exception);
        } finally {
            $this->getLockItemManager()->remove();
        }
    }

    private function processAllTasks(): void
    {
        if ($this->allowedTasks === null) {
            $taskGroup = $this->cronManager->getNextTaskGroup();
            $this->cronManager->setLastExecutedTaskGroup($taskGroup);

            $tasks = $this->taskCollection->getGroupTasks($taskGroup);
        } else {
            /**
             * Developer cron runner
             */
            $tasks = [];
            foreach ($this->allowedTasks as $taskNick) {
                $tasks[] = $this->taskCollection->getTaskByNick($taskNick);
            }
        }

        foreach ($tasks as $taskDefinition) {
            try {
                $taskProcessor = $this->taskProcessorFactory->create(
                    $taskDefinition,
                    $this->getInitiator(),
                    $this->getOperationHistory(),
                );

                $taskProcessor->process();
            } catch (\Throwable $exception) {
                $this->processException($exception);
            }
        }
    }

    private function afterEnd(): void
    {
        $this->getOperationHistory()->stop();
    }

    private function tryRetrieveLockManager(): bool
    {
        try {
            $this->getLockItemManager();

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function getLockItemManager(): \M2E\TikTokShop\Model\Lock\Item\Manager
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->lockItemManager)) {
            return $this->lockItemManager;
        }

        $lockItemManager = $this->lockItemManagerFactory->create(self::LOCK_ITEM_NICK);
        if (!$lockItemManager->isExist()) {
            return $this->lockItemManager = $lockItemManager;
        }

        if ($lockItemManager->isInactiveMoreThanSeconds(self::LOCK_MAX_INACTIVE_SECONDS)) {
            $lockItemManager->remove();

            return $this->lockItemManager = $lockItemManager;
        }

        throw new \LogicException('Lock Item Manager unable to retrieve lock.');
    }

    private function getInitializationLockManager(): \M2E\TikTokShop\Model\Lock\Transactional\Manager
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->initializationLockManager)) {
            return $this->initializationLockManager;
        }

        $lockTransactionalManager = $this->lockTransactionalManagerFactory->create(
            self::INITIALIZATION_TRANSACTIONAL_LOCK_NICK,
        );

        return $this->initializationLockManager = $lockTransactionalManager;
    }

    // ---------------------------------------

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
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->operationHistory)) {
            return $this->operationHistory;
        }

        return $this->operationHistory = $this->operationHistoryFactory->create();
    }

    private function makeLockItemShutdownFunction(\M2E\TikTokShop\Model\Lock\Item\Manager $lockItemManager): void
    {
        $lockItem = $this->lockItemRepository->findByNick($lockItemManager->getNick());
        if ($lockItem === null) {
            return;
        }

        $id = (int)$lockItem->getId();

        register_shutdown_function(
            function () use ($id) {
                $error = error_get_last();
                if ($error === null || !in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
                    return;
                }

                $lockItem = $this->lockItemRepository->findById($id);
                if ($lockItem !== null) {
                    $this->lockItemRepository->remove($lockItem);
                }
            }
        );
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
