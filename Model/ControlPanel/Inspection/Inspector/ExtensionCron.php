<?php

namespace M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector;

use M2E\TikTokShop\Helper\Module\Cron;

class ExtensionCron implements \M2E\TikTokShop\Model\ControlPanel\Inspection\InspectorInterface
{
    private \M2E\TikTokShop\Helper\Module\Cron $moduleCron;
    private \M2E\TikTokShop\Model\ControlPanel\Inspection\Issue\Factory $issueFactory;
    private \M2E\TikTokShop\Model\Config\Manager $config;

    public function __construct(
        \M2E\TikTokShop\Model\Config\Manager $config,
        \M2E\TikTokShop\Helper\Module\Cron $moduleCron,
        \M2E\TikTokShop\Model\ControlPanel\Inspection\Issue\Factory $issueFactory
    ) {
        $this->config = $config;
        $this->moduleCron = $moduleCron;
        $this->issueFactory = $issueFactory;
    }

    public function process()
    {
        $issues = [];
        $helper = $this->moduleCron;

        if ($helper->getLastRun() === null) {
            $issues[] = $this->issueFactory->create(
                "Cron [{$helper->getRunner()}] does not work"
            );
        } elseif ($helper->isLastRunMoreThan(1800)) {
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $cron = $helper->getLastRun();
            $diff = round(($now->getTimestamp() - $cron->getTimestamp()) / 60, 0);

            $issues[] = $this->issueFactory->create(
                "Cron [{$helper->getRunner()}] is not working for {$diff} min",
                <<<HTML
Last run: {$helper->getLastRun()->format('Y-m-d H:i:s')}
Now:      {$now->format('Y-m-d H:i:s')}
HTML
            );
        }

        if ($this->config->getGroupValue(sprintf('/cron/%s/', Cron::RUNNER), 'disabled')) {
            $message = sprintf('Cron [%s] is disabled by developer', Cron::RUNNER);
            $issues[] = $this->issueFactory->create($message);
        }

        return $issues;
    }
}
