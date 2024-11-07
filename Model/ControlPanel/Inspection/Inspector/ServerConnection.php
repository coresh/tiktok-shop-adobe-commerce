<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector;

use M2E\TikTokShop\Model\ControlPanel\Inspection\InspectorInterface;
use M2E\TikTokShop\Model\Exception\Connection;
use M2E\TikTokShop\Model\ControlPanel\Inspection\Issue\Factory as IssueFactory;

class ServerConnection implements InspectorInterface
{
    private IssueFactory $issueFactory;
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;

    public function __construct(
        IssueFactory $issueFactory,
        \M2E\TikTokShop\Model\Connector\Client\Single $serverClient
    ) {
        $this->issueFactory = $issueFactory;
        $this->serverClient = $serverClient;
    }

    public function process(): array
    {
        $issues = [];

        try {
            $this->serverClient->process(new \M2E\TikTokShop\Model\TikTokShop\Connector\Server\CheckStateCommand());
        } catch (Connection $exception) {
            $issues[] = $this->issueFactory->create(
                $exception->getMessage(),
                $exception->getCurlInfo()
            );
        }

        return $issues;
    }
}
