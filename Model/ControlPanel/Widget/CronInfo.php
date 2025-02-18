<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ControlPanel\Widget;

class CronInfo implements \M2E\Core\Model\ControlPanel\Widget\CronInfoInterface
{
    private \M2E\TikTokShop\Model\Cron\Config $cronConfig;
    private \M2E\TikTokShop\Model\Cron\Manager $cronManager;

    public function __construct(
        \M2E\TikTokShop\Model\Cron\Config $cronConfig,
        \M2E\TikTokShop\Model\Cron\Manager $cronManager
    ) {
        $this->cronConfig = $cronConfig;
        $this->cronManager = $cronManager;
    }

    public function isCronWorking(): bool
    {
        return !$this->cronManager->isCronLastRunMoreThan(3600);
    }

    public function getCronLastRunTime(): ?\DateTimeInterface
    {
        return $this->cronManager->getCronLastRun();
    }

    public function isRunnerTypeMagento(): bool
    {
        return $this->cronConfig->getActiveRunner() === \M2E\TikTokShop\Model\Cron\Config::RUNNER_MAGENTO;
    }

    public function isRunnerTypeDeveloper(): bool
    {
        return false;
    }

    public function isRunnerTypeServiceController(): bool
    {
        return false;
    }

    public function isRunnerTypeServicePub(): bool
    {
        return false;
    }

    public function isMagentoCronDisabled(): bool
    {
        return $this->cronConfig->isRunnerDisabled(\M2E\TikTokShop\Model\Cron\Config::RUNNER_MAGENTO);
    }

    public function isControllerCronDisabled(): bool
    {
        return false;
    }

    public function isServicePubDisabled(): bool
    {
        return false;
    }

    public function getServiceAuthKey(): string
    {
        return '';
    }
}
