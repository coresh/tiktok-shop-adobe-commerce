<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron\Task\Product;

class InspectDirectChangesTask implements
    \M2E\Core\Model\Cron\TaskHandlerInterface,
    \M2E\Core\Model\Cron\Task\PossibleRunInterface
{
    public const NICK = 'product/inspect_direct_changes';

    private \M2E\TikTokShop\Model\Product\InspectDirectChanges $inspectDirectChanges;
    private \M2E\TikTokShop\Model\Product\InspectDirectChanges\Config $config;

    public function __construct(
        \M2E\TikTokShop\Model\Product\InspectDirectChanges\Config $config,
        \M2E\TikTokShop\Model\Product\InspectDirectChanges $inspectDirectChanges
    ) {
        $this->config = $config;
        $this->inspectDirectChanges = $inspectDirectChanges;
    }

    public function isPossibleToRun(): bool
    {
        return $this->config->isEnableProductInspectorMode();
    }

    public function process($context): void
    {
        $this->inspectDirectChanges->process();
    }
}
