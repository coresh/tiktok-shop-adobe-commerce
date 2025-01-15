<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron;

class TaskRepository
{
    private const GROUP_CHANNEL = 'channel';
    private const GROUP_SYSTEM = 'system';

    private static array $registeredTasks = [

        #region channel
        self::GROUP_CHANNEL => [
            Task\Order\ReserveCancel::NICK => Task\Order\ReserveCancel::class,
            Task\Order\SyncTask::NICK => Task\Order\SyncTask::class,
            Task\Order\UploadByUser::NICK => Task\Order\UploadByUser::class,
            Task\Order\UpdateTask::NICK => Task\Order\UpdateTask::class,
            Task\Order\CancelTask::NICK => Task\Order\CancelTask::class,
            Task\Order\CreateFailedTask::NICK => Task\Order\CreateFailedTask::class,
            Task\InventorySyncTask::NICK => Task\InventorySyncTask::class,
            Task\InstructionsProcess::NICK => Task\InstructionsProcess::class,
            Task\ProcessScheduledActionsTask::NICK => Task\ProcessScheduledActionsTask::class,
            Task\Product\StopQueue::NICK => Task\Product\StopQueue::class,
            Task\Product\PromotionSync::NICK => Task\Product\PromotionSync::class,
        ],
        #endregion

        #region system
        self::GROUP_SYSTEM => [
            Task\System\Servicing\Synchronize::NICK => Task\System\Servicing\Synchronize::class,
            Task\Magento\Product\DetectDirectlyDeleted::NICK => Task\Magento\Product\DetectDirectlyDeleted::class,
            Task\Magento\Product\DetectSpecialPriceEndDate::NICK
                => Task\Magento\Product\DetectSpecialPriceEndDate::class,
            Task\System\Processing\DownloadDataTask::NICK => Task\System\Processing\DownloadDataTask::class,
            Task\System\Processing\ProcessDataTask::NICK => Task\System\Processing\ProcessDataTask::class,
            Task\System\ClearOldLogs::NICK => Task\System\ClearOldLogs::class,
        ],
        #endregion
    ];

    private array $allTasks;
    private array $groups;
    private array $nicks;

    public function __construct()
    {
        $allTasks = [];
        $groups = [];
        $nicks = [];
        foreach (self::$registeredTasks as $group => $tasks) {
            array_push($allTasks, ...array_values($tasks));
            foreach ($tasks as $nick => $class) {
                $groups[$class] = $group;
                $nicks[$class] = $nick;
            }
        }

        $this->allTasks = $allTasks;
        $this->groups = $groups;
        $this->nicks = $nicks;
    }

    public function getTaskGroup(string $className): string
    {
        return $this->groups[$className];
    }

    public function getNick(string $className): string
    {
        return $this->nicks[$className];
    }

    public function getRegisteredTasks(): array
    {
        return $this->allTasks;
    }

    /**
     * @param string $group
     *
     * @return string[]
     */
    public function getGroupTasks(string $group): array
    {
        $result = [];
        foreach ($this->groups as $class => $taskGroup) {
            if ($taskGroup === $group) {
                $result[] = $class;
            }
        }

        return $result;
    }

    /**
     * @return string[]
     */
    public function getRegisteredGroups(): array
    {
        return array_keys(self::$registeredTasks);
    }
}
