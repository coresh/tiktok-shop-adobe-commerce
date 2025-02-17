<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Runner;

use M2E\TikTokShop\Model\Cron\AbstractRunner;

class Developer extends AbstractRunner
{
    private array $allowedTasks;

    public function getNick(): string
    {
        return 'developer';
    }

    public function getInitiator(): int
    {
        return \M2E\TikTokShop\Helper\Data::INITIATOR_DEVELOPER;
    }

    /**
     * @param string[] $tasks
     *
     * @return $this
     */
    public function setAllowedTasks(array $tasks): self
    {
        $this->allowedTasks = $tasks;

        return $this;
    }

    public function process(): void
    {
        // @codingStandardsIgnoreLine
        session_write_close();
        parent::process();
    }

    protected function getStrategy(): \M2E\TikTokShop\Model\Cron\Strategy
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->allowedTasks)) {
            throw new \LogicException('Developer strategy has not been set.');
        }

        $strategy = parent::getStrategy();
        $strategy->setAllowedTasks($this->allowedTasks);

        return $strategy;
    }

    protected function isPossibleToRun(): bool
    {
        return true;
    }

    protected function canProcessRunner(): bool
    {
        return true;
    }

    protected function setLastRun(): void
    {
    }

    protected function setLastAccess(): void
    {
    }
}
