<?php

namespace M2E\TikTokShop\Helper\Module;

class Cron
{
    public const RUNNER = 'magento';

    private \M2E\TikTokShop\Model\Cron\Manager $cronManager;
    private \M2E\TikTokShop\Model\Config\Manager $config;

    public function __construct(
        \M2E\TikTokShop\Model\Cron\Manager $cronManager,
        \M2E\TikTokShop\Model\Config\Manager $config
    ) {
        $this->config = $config;
        $this->cronManager = $cronManager;
    }

    public function isModeEnabled(): bool
    {
        return (bool)$this->getConfigValue('mode');
    }

    // ----------------------------------------

    public function getRunner(): string
    {
        return self::RUNNER;
    }

    // ---------------------------------------

    public function getLastAccess(): ?\DateTime
    {
        return $this->cronManager->getLastAccess('/cron/');
    }

    public function setLastAccess(): void
    {
        $this->cronManager->setLastAccess('/cron/');
    }

    // ---------------------------------------

    public function getLastRun(): ?\DateTime
    {
        return $this->cronManager->getLastRun('/cron/');
    }

    public function setLastRun(): void
    {
        $this->cronManager->setLastRun('/cron/');
    }

    // ---------------------------------------

    public function isLastRunMoreThan($interval, $isHours = false)
    {
        if ($isHours) {
            $interval *= 3600;
        }

        $lastRun = $this->getLastRun();
        if ($lastRun === null) {
            return false;
        }

        $lastRunTimestamp = (int)$lastRun->format('U');

        return \M2E\TikTokShop\Helper\Date::createCurrentGmt()->getTimestamp() > $lastRunTimestamp + $interval;
    }

    //----------------------------------------

    public function getLastExecutedTaskGroup()
    {
        return $this->getConfigValue('last_executed_task_group');
    }

    public function setLastExecutedTaskGroup($groupNick)
    {
        $this->setConfigValue('last_executed_task_group', $groupNick);
    }

    /**
     * @return mixed|null
     */
    private function getConfigValue(string $key)
    {
        return $this->config->getGroupValue('/cron/', $key);
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return bool
     */
    private function setConfigValue(string $key, $value)
    {
        return $this->config->setGroupValue('/cron/', $key, $value);
    }
}
