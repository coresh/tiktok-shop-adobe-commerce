<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron;

class Config
{
    public const CONFIG_GROUP = '/cron/';

    public const CONFIG_KEY_MODE = 'mode';

    public const CONFIG_KEY_RUNNER = 'runner';
    public const CONFIG_KEY_RUNNER_DISABLED = 'disabled';

    public const CONFIG_KEY_TASK_MODE = 'mode';
    public const CONFIG_KEY_TASK_INTERVAL = 'interval';

    public const RUNNER_MAGENTO = 'magento';

    private \M2E\TikTokShop\Model\Config\Manager $config;

    public function __construct(\M2E\TikTokShop\Model\Config\Manager $config)
    {
        $this->config = $config;
    }

    // ----------------------------------------

    public function isEnabled(): bool
    {
        return (bool)(int)$this->config->getGroupValue(self::CONFIG_GROUP, self::CONFIG_KEY_MODE);
    }

    // ----------------------------------------

    public function isRunnerDisabled(string $runnerNick): bool
    {
        return (bool)(int)$this->config->getGroupValue(
            self::getRunnerConfigGroup($runnerNick),
            self::CONFIG_KEY_RUNNER_DISABLED
        );
    }

    public static function getRunnerConfigGroup(string $runnerNick): string
    {
        return sprintf('%s%s/', self::CONFIG_GROUP, self::prepareNick($runnerNick));
    }

    public function getActiveRunner(): string
    {
        return (string)$this->config->getGroupValue(self::CONFIG_GROUP, self::CONFIG_KEY_RUNNER);
    }

    // ----------------------------------------

    public function isTaskEnabled(string $taskNick): bool
    {
        $value = $this->config->getGroupValue(self::getTaskConfigGroup($taskNick), self::CONFIG_KEY_TASK_MODE);
        if ($value === null) {
            return true;
        }

        return (bool)(int)$value;
    }

    public function getTaskConfiguredIntervalInSeconds(string $taskNick): ?int
    {
        $value = $this->config->getGroupValue(self::getTaskConfigGroup($taskNick), self::CONFIG_KEY_TASK_INTERVAL);
        if ($value === null) {
            return null;
        }

        return (int)$value;
    }

    public static function getTaskConfigGroup(string $taskNick): string
    {
        return sprintf('/cron/task/%s/', self::prepareNick($taskNick));
    }

    private static function prepareNick(string $nick): string
    {
        return strtolower(trim($nick, '/'));
    }
}
