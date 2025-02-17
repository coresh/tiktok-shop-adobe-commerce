<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task;

class ProcessScheduledActionsTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'scheduled_actions/process';

    private \M2E\TikTokShop\Model\ScheduledAction\Processor $processor;

    public function __construct(
        \M2E\TikTokShop\Model\ScheduledAction\Processor $processor
    ) {
        $this->processor = $processor;
    }

    public function process($context): void
    {
        $this->processor->process();
    }
}
