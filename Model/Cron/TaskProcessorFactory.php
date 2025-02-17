<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron;

class TaskProcessorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Core\Model\Cron\TaskDefinition $taskDefinition,
        int $initiator,
        \M2E\TikTokShop\Model\Cron\OperationHistory $parentOperationHistory
    ): TaskProcessor {
        return $this->objectManager->create(
            TaskProcessor::class,
            [
                'taskDefinition' => $taskDefinition,
                'initiator' => $initiator,
                'parentOperationHistory' => $parentOperationHistory,
            ]
        );
    }
}
