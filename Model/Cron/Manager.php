<?php

namespace M2E\TikTokShop\Model\Cron;

class Manager
{
    private \M2E\TikTokShop\Model\Registry\Manager $registryManager;
    /** @var \M2E\TikTokShop\Model\Cron\TaskCollection */
    private TaskCollection $cronTaskCollection;

    public function __construct(
        \M2E\TikTokShop\Model\Registry\Manager $registryManager,
        \M2E\TikTokShop\Model\Cron\TaskCollection $cronTaskCollection
    ) {
        $this->registryManager = $registryManager;
        $this->cronTaskCollection = $cronTaskCollection;
    }

    // ----------------------------------------

    public function setCronLastAccess(): void
    {
        $this->setCurrentDateTimeValue($this->createCronLastAccessKey());
    }

    public function getCronLastAccessKey(): ?\DateTime
    {
        return $this->getDateTimeValue($this->createCronLastAccessKey());
    }

    private function createCronLastAccessKey(): string
    {
        return '/cron/last_access/';
    }

    public function isCronLastRunMoreThan($interval): bool
    {
        $lastRun = $this->getCronLastRun();
        if ($lastRun === null) {
            return false;
        }

        $lastRunTimestamp = $lastRun->getTimestamp();

        return \M2E\Core\Helper\Date::createCurrentGmt()->getTimestamp() > $lastRunTimestamp + $interval;
    }

    public function setCronLastRun(): void
    {
        $this->setCurrentDateTimeValue($this->createCronLastRunKey());
    }

    public function getCronLastRun(): ?\DateTime
    {
        return $this->getDateTimeValue($this->createCronLastRunKey());
    }

    private function createCronLastRunKey(): string
    {
        return '/cron/last_run/';
    }

    // ----------------------------------------

    public function setTaskLastAccess(string $taskNick): void
    {
        $this->setCurrentDateTimeValue($this->createTaskLastAccessKey($taskNick));
    }

    public function getTaskLastAccess(string $taskNick): ?\DateTime
    {
        return $this->getDateTimeValue($this->createTaskLastAccessKey($taskNick));
    }

    private function createTaskLastAccessKey(string $taskName): string
    {
        return sprintf('/cron/task/%s/last_access/', strtolower(trim($taskName, '/')));
    }

    // ----------------------------------------

    public function setTaskLastRun(string $taskNick): void
    {
        $this->setCurrentDateTimeValue($this->createTaskLastRunKey($taskNick));
    }

    public function getTaskLastRun(string $taskNick): ?\DateTime
    {
        return $this->getDateTimeValue($this->createTaskLastRunKey($taskNick));
    }

    private function createTaskLastRunKey(string $taskName): string
    {
        return sprintf('/cron/task/%s/last_run/', strtolower(trim($taskName, '/')));
    }

    // ----------------------------------------

    public function getNextTaskGroup(): string
    {
        $allGroups = $this->cronTaskCollection->getRegisteredGroups();
        $firstGroup = reset($allGroups);
        $lastGroup = end($allGroups);

        $lastExecuted = $this->registryManager->getValue($this->createLastExecutedTaskGroupKey());
        if (empty($lastExecuted)) {
            return $firstGroup;
        }

        $lastExecutedIndex = array_search($lastExecuted, $allGroups, true);

        if (
            $lastExecutedIndex === false
            || $lastGroup === $lastExecuted
        ) {
            return $firstGroup;
        }

        return $allGroups[$lastExecutedIndex + 1];
    }

    public function setLastExecutedTaskGroup(string $taskGroup): void
    {
        $this->registryManager->setValue($this->createLastExecutedTaskGroupKey(), $taskGroup);
    }

    private function createLastExecutedTaskGroupKey(): string
    {
        return '/cron/last_executed_task_group/';
    }

    // ----------------------------------------

    private function setCurrentDateTimeValue(string $key): void
    {
        $this->registryManager->setValue(
            $key,
            \M2E\TikTokShop\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')
        );
    }

    private function getDateTimeValue(string $key): ?\DateTime
    {
        $value = $this->registryManager->getValue($key);
        if ($value === null) {
            return null;
        }

        return \M2E\Core\Helper\Date::createDateGmt($value);
    }
}
