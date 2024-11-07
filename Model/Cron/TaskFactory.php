<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Cron;

class TaskFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createByClassName(
        string $className,
        int $initiator,
        \M2E\TikTokShop\Model\Cron\OperationHistory $operationHistory,
        \M2E\TikTokShop\Model\Lock\Item\Manager $lockItemManager
    ): AbstractTask {
        /** @var AbstractTask $task */
        $task = $this->objectManager->create($className);

        if (!$task instanceof AbstractTask) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Invalid instance');
        }

        $task->setInitiator($initiator);
        $task->setParentOperationHistory($operationHistory);
        $task->setLockItemManager($lockItemManager);

        return $task;
    }
}
