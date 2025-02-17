<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ControlPanel;

class CronTaskProvider implements \M2E\Core\Model\ControlPanel\Cron\TaskProviderInterface
{
    private \M2E\TikTokShop\Model\Cron\TaskCollection $taskCollection;

    private array $tasks;

    public function __construct(\M2E\TikTokShop\Model\Cron\TaskCollection $taskCollection)
    {
        $this->taskCollection = $taskCollection;
    }

    public function getExtensionModuleName(): string
    {
        return Extension::NAME;
    }

    public function getTasks(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->tasks)) {
            return $this->tasks;
        }

        $tasks = [];
        foreach ($this->taskCollection->getAllTasks() as $taskDefinition) {
            $tasks[] = new \M2E\Core\Model\ControlPanel\CronTask(
                $taskDefinition->getGroup(),
                $taskDefinition->getNick(),
                $taskDefinition->getNick(),
            );
        }

        return $this->tasks = $tasks;
    }
}
