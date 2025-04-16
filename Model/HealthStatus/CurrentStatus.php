<?php

namespace M2E\TikTokShop\Model\HealthStatus;

use M2E\TikTokShop\Model\HealthStatus\Task\Result\Set;

class CurrentStatus
{
    private \M2E\TikTokShop\Model\Registry\Manager $registry;

    public function __construct(
        \M2E\TikTokShop\Model\Registry\Manager $registry
    ) {
        $this->registry = $registry;
    }

    public function get(): int
    {
        return (int)$this->registry->getValue('/health_status/current_status/');
    }

    public function set(Set $resultSet): void
    {
        $this->registry->setValue(
            '/health_status/current_status/',
            (string)$resultSet->getWorstState()
        );

        $details = [];
        foreach ($resultSet->getByKeys() as $result) {
            $details[$result->getTaskHash()] = [
                'result' => $result->getTaskResult(),
                'data' => $result->getTaskData(),
            ];
        }

        $this->registry->setValue(
            '/health_status/details/',
            \M2E\Core\Helper\Json::encode($details)
        );
    }
}
