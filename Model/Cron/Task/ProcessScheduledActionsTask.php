<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task;

class ProcessScheduledActionsTask extends \M2E\TikTokShop\Model\Cron\AbstractTask
{
    public const NICK = 'scheduled_actions/process';

    private \M2E\TikTokShop\Model\ScheduledAction\Processor $processor;

    public function __construct(
        \M2E\TikTokShop\Model\Cron\Manager $cronManager,
        \M2E\TikTokShop\Model\Synchronization\LogService $syncLogger,
        \M2E\TikTokShop\Model\ScheduledAction\Processor $processor,
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
            $resource
        );

        $this->processor = $processor;
    }

    protected function getNick(): string
    {
        return self::NICK;
    }

    protected function performActions(): void
    {
        $this->processor->process();
    }
}
