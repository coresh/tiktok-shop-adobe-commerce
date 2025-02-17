<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector;

class ExtensionCron implements \M2E\Core\Model\ControlPanel\Inspection\InspectorInterface
{
    private \M2E\Core\Model\ControlPanel\Inspection\IssueFactory $issueFactory;
    private \M2E\TikTokShop\Model\Cron\Manager $cronManager;
    private \M2E\TikTokShop\Model\Cron\Config $cronConfig;

    public function __construct(
        \M2E\TikTokShop\Model\Cron\Manager $cronManager,
        \M2E\TikTokShop\Model\Cron\Config $cronConfig,
        \M2E\Core\Model\ControlPanel\Inspection\IssueFactory $issueFactory
    ) {
        $this->issueFactory = $issueFactory;
        $this->cronManager = $cronManager;
        $this->cronConfig = $cronConfig;
    }

    public function process(): array
    {
        $issues = [];

        if ($this->cronManager->getCronLastRun() === null) {
            $issues[] = $this->issueFactory->create(
                "Cron [{$this->cronConfig->getActiveRunner()}] does not work"
            );
        } elseif ($this->cronManager->isCronLastRunMoreThan(1800)) {
            $now = \M2E\Core\Helper\Date::createCurrentGmt();

            /** @var \DateTime $cronLastRun */
            $cronLastRun = $this->cronManager->getCronLastRun();

            $diff = round(($now->getTimestamp() - $cronLastRun->getTimestamp()) / 60, 0);

            $issues[] = $this->issueFactory->create(
                "Cron [{$this->cronConfig->getActiveRunner()}] is not working for {$diff} min",
                <<<HTML
Last run: {$cronLastRun->format('Y-m-d H:i:s')}
Now:      {$now->format('Y-m-d H:i:s')}
HTML
            );
        }

        if ($this->cronConfig->isRunnerDisabled(\M2E\TikTokShop\Model\Cron\Config::RUNNER_MAGENTO)) {
            $message = sprintf('Cron [%s] is disabled by developer', \M2E\TikTokShop\Model\Cron\Config::RUNNER_MAGENTO);
            $issues[] = $this->issueFactory->create($message);
        }

        return $issues;
    }
}
