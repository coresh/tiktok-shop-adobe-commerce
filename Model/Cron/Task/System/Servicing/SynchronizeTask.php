<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task\System\Servicing;

class SynchronizeTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'system/servicing/synchronize';

    private \M2E\TikTokShop\Model\Servicing\Dispatcher $dispatcher;

    public function __construct(
        \M2E\TikTokShop\Model\Servicing\Dispatcher $dispatcher
    ) {
        $this->dispatcher = $dispatcher;
    }

    public function process($context): void
    {
        $this->dispatcher->process();
    }
}
