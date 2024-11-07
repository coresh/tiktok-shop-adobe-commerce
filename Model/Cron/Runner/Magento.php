<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Runner;

use M2E\TikTokShop\Model\Cron\AbstractRunner;

class Magento extends AbstractRunner
{
    private static bool $isRunning = false;

    public const MIN_DISTRIBUTION_EXECUTION_TIME = 300;
    public const MAX_DISTRIBUTION_WAIT_INTERVAL = 59;

    public function getNick(): ?string
    {
        return \M2E\TikTokShop\Helper\Module\Cron::RUNNER;
    }

    public function getInitiator(): int
    {
        return \M2E\TikTokShop\Helper\Data::INITIATOR_UNKNOWN;
    }

    protected function isPossibleToRun(): bool
    {
        if (self::$isRunning) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    protected function beforeStart(): void
    {
        /*
         * Magento can execute TikTokShop cron multiple times in same php process.
         * It can cause problems with items that were cached in first execution.
         */
        // ---------------------------------------
        self::$isRunning = true;
        // ---------------------------------------

        parent::beforeStart();

        $this->distributeLoadIfNeed();
    }

    private function distributeLoadIfNeed(): void
    {
        $maxExecutionTime = (int)ini_get('max_execution_time');

        if ($maxExecutionTime <= 0 || $maxExecutionTime < self::MIN_DISTRIBUTION_EXECUTION_TIME) {
            return;
        }

        sleep(rand(0, self::MAX_DISTRIBUTION_WAIT_INTERVAL));
    }
}
