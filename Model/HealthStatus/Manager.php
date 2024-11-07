<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\HealthStatus;

class Manager
{
    private Task\Result\SetFactory $resultSetFactory;
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\TikTokShop\Model\HealthStatus\Task\Result\SetFactory $resultSetFactory,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper
    ) {
        $this->resultSetFactory = $resultSetFactory;
        $this->objectManager = $objectManager;
        $this->exceptionHelper = $exceptionHelper;
    }

    public function doCheck(): Task\Result\Set
    {
        $resultSet = $this->resultSetFactory->create();

        foreach ($this->getTasks() as $taskClass) {
            try {
                /** @var \M2E\TikTokShop\Model\HealthStatus\Task\AbstractModel $taskObject */
                $taskObject = $this->objectManager->create($taskClass);
                $resultSet->add($taskObject->process());
            } catch (\Throwable $throwable) {
                $this->exceptionHelper->process($throwable);
            }
        }

        return $resultSet;
    }

    private function getTasks(): array
    {
        return array_merge($this->getInfoTasks(), $this->getIssueTasks());
    }

    private function getInfoTasks(): array
    {
        return [];
    }

    private function getIssueTasks(): array
    {
        return [
            \M2E\TikTokShop\Model\HealthStatus\Task\Database\MysqlInfo\CrashedTables::class,
            \M2E\TikTokShop\Model\HealthStatus\Task\Database\MysqlInfo\TablesStructure::class,
            \M2E\TikTokShop\Model\HealthStatus\Task\Server\Status\SystemLogs::class,
            \M2E\TikTokShop\Model\HealthStatus\Task\Orders\MagentoCreationFailed::class,
        ];
    }
}
