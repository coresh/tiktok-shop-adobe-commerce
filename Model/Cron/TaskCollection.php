<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron;

class TaskCollection
{
    private const GROUP_CHANNEL = 'channel';
    private const GROUP_SYSTEM = 'system';

    private array $allGroups = [
        self::GROUP_CHANNEL,
        self::GROUP_SYSTEM,
    ];

    /** @var \M2E\Core\Model\Cron\TaskDefinition[] */
    private array $allTasks;

    /**
     * @return string[]
     */
    public function getRegisteredGroups(): array
    {
        return $this->allGroups;
    }

    /**
     * @param string $group
     *
     * @return \M2E\Core\Model\Cron\TaskDefinition[]
     */
    public function getGroupTasks(string $group): array
    {
        $result = [];
        foreach ($this->getAllTasks() as $definition) {
            if ($definition->getGroup() === $group) {
                $result[] = $definition;
            }
        }

        return $result;
    }

    public function getTaskByNick(string $nick): \M2E\Core\Model\Cron\TaskDefinition
    {
        foreach ($this->getAllTasks() as $definition) {
            if ($definition->getNick() === $nick) {
                return $definition;
            }
        }

        throw new \LogicException('Unknown cron task ' . $nick);
    }

    // ----------------------------------------

    /**
     * @return \M2E\Core\Model\Cron\TaskDefinition[]
     */
    public function getAllTasks(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->allTasks)) {
            return $this->allTasks;
        }

        /** @var \M2E\Core\Model\Cron\TaskDefinition[] $list */
        $list = [
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_CHANNEL,
                Task\Order\ReserveCancelTask::NICK,
                60,
                Task\Order\ReserveCancelTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_CHANNEL,
                Task\Order\SyncTask::NICK,
                300,
                Task\Order\SyncTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_CHANNEL,
                Task\Order\UploadByUser::NICK,
                60,
                Task\Order\UploadByUser::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_CHANNEL,
                Task\Order\UpdateTask::NICK,
                60,
                Task\Order\UpdateTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_CHANNEL,
                Task\Order\CancelTask::NICK,
                60,
                Task\Order\CancelTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_CHANNEL,
                Task\Order\CreateFailedTask::NICK,
                60,
                Task\Order\CreateFailedTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_CHANNEL,
                Task\InventorySyncTask::NICK,
                300,
                Task\InventorySyncTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_CHANNEL,
                Task\InstructionsProcessTask::NICK,
                60,
                Task\InstructionsProcessTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_CHANNEL,
                Task\ProcessScheduledActionsTask::NICK,
                60,
                Task\ProcessScheduledActionsTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_CHANNEL,
                Task\Product\StopQueueTask::NICK,
                3600,
                Task\Product\StopQueueTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_CHANNEL,
                Task\Product\PromotionSyncTask::NICK,
                28800, // 8 hours
                Task\Product\PromotionSyncTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_CHANNEL,
                Task\Product\InspectDirectChangesTask::NICK,
                60,
                Task\Product\InspectDirectChangesTask::class,
            ),
            // ----------------------------------------
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_SYSTEM,
                Task\System\Servicing\SynchronizeTask::NICK,
                300,
                Task\System\Servicing\SynchronizeTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_SYSTEM,
                Task\Magento\Product\DetectDirectlyDeletedTask::NICK,
                60,
                Task\Magento\Product\DetectDirectlyDeletedTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_SYSTEM,
                Task\Magento\Product\DetectSpecialPriceEndDateTask::NICK,
                7200, // 2 hours
                Task\Magento\Product\DetectSpecialPriceEndDateTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_SYSTEM,
                Task\System\Processing\DownloadDataTask::NICK,
                60,
                Task\System\Processing\DownloadDataTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_SYSTEM,
                Task\System\Processing\ProcessDataTask::NICK,
                60,
                Task\System\Processing\ProcessDataTask::class,
            ),
            new \M2E\Core\Model\Cron\TaskDefinition(
                self::GROUP_SYSTEM,
                Task\System\ClearOldLogsTask::NICK,
                86400, // 1 day
                Task\System\ClearOldLogsTask::class,
            )
        ];

        // ----------------------------------------

        $tasks = [];
        foreach ($list as $definition) {
            if (isset($tasks[$definition->getNick()])) {
                throw new \LogicException('Task already registered');
            }

            $tasks[$definition->getNick()] = $definition;
        }

        return $this->allTasks = array_values($tasks);
    }
}
