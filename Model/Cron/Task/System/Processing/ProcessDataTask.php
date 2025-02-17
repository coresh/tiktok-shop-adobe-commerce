<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task\System\Processing;

class ProcessDataTask implements \M2E\Core\Model\Cron\TaskHandlerInterface
{
    public const NICK = 'processing/process/data';

    private \M2E\TikTokShop\Model\Processing\ProcessResult\Partial $processResultPartial;
    private \M2E\TikTokShop\Model\Processing\Lock\ClearMissed $lockClearMissed;

    public function __construct(
        \M2E\TikTokShop\Model\Processing\ProcessResult\Partial $processResultPartial,
        \M2E\TikTokShop\Model\Processing\Lock\ClearMissed $lockClearMissed
    ) {
        $this->processResultPartial = $processResultPartial;
        $this->lockClearMissed = $lockClearMissed;
    }

    public function process($context): void
    {
        $this->processResultPartial->processExpired();

        $this->processResultPartial->processData();

        $this->lockClearMissed->process();
    }
}
