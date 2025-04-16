<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Servicing;

class Dispatcher
{
    private const DEFAULT_INTERVAL = 3600;
    private const SERVER_TASKS_CLASS = [
        Task\License::NAME => Task\License::class,
        Task\Settings::NAME => Task\Settings::class
    ];

    private \M2E\TikTokShop\Helper\Module\Exception $helperException;
    private \M2E\TikTokShop\Model\Registry\Manager $registryManager;
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverConnector;

    public function __construct(
        \M2E\TikTokShop\Helper\Module\Exception $helperException,
        \M2E\TikTokShop\Model\Registry\Manager $registryManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\TikTokShop\Model\Connector\Client\Single $serverConnector
    ) {
        $this->helperException = $helperException;
        $this->registryManager = $registryManager;
        $this->objectManager = $objectManager;
        $this->serverConnector = $serverConnector;
    }

    // ---------------------------------------

    /**
     * @throws \Exception
     */
    public function process($taskCodes = null): void
    {
        if (!is_array($taskCodes)) {
            $taskCodes = $this->getRegisteredTasks();
        }

        $lastUpdate = $this->getLastUpdateDate();
        $currentDate = \M2E\Core\Helper\Date::createCurrentGmt();

        if (
            $lastUpdate !== null
            && $lastUpdate->getTimestamp() + self::DEFAULT_INTERVAL > $currentDate->getTimestamp()
        ) {
            return;
        }

        $this->setLastUpdateDateTime();
        $this->processTasks($taskCodes);
    }

    /**
     * @throws \Exception
     */
    public function processFastTasks(): void
    {
        $this->process([Task\License::NAME]);
    }

    // ----------------------------------------

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     */
    public function processTask(string $taskCode): void
    {
        $this->processTasks([$taskCode]);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     */
    private function processTasks(array $taskCodes): void
    {
        $this->helperException->setFatalErrorHandler();
        $tasksModel = $this->getTasksModels($taskCodes);

        $requestData = [];
        foreach ($tasksModel as $taskModel) {
            $requestData[$taskModel->getServerTaskName()] = $taskModel->getRequestData();
        }

        $command = new \M2E\Core\Model\Server\Connector\ServicingCommand($requestData);
        /** @var \M2E\Core\Model\Connector\Response $response */
        $response = $this->serverConnector->process($command);

        $responseData = $response->getResponseData();

        foreach ($tasksModel as $taskModel) {
            if (
                !isset($responseData[$taskModel->getServerTaskName()])
                || !is_array($responseData[$taskModel->getServerTaskName()])
            ) {
                continue;
            }

            $taskModel->processResponseData($responseData[$taskModel->getServerTaskName()]);
        }
    }

    // ---------------------------------------

    /**
     * @param array $taskCodes
     *
     * @return \M2E\TikTokShop\Model\Servicing\TaskInterface[]
     */
    private function getTasksModels(array $taskCodes): array
    {
        $result = [];

        foreach ($this->getRegisteredTasks() as $taskName) {
            if (!in_array($taskName, $taskCodes)) {
                continue;
            }

            $taskModel = $this->getTaskModel($taskName);

            if (!$taskModel->isAllowed()) {
                continue;
            }
            $result[] = $taskModel;
        }

        return $result;
    }

    // ---------------------------------------

    /**
     * @param string $taskName
     *
     * @return \M2E\TikTokShop\Model\Servicing\TaskInterface
     */
    private function getTaskModel(string $taskName): TaskInterface
    {
        $taskClass = self::SERVER_TASKS_CLASS[$taskName];

        /** @var \M2E\TikTokShop\Model\Servicing\TaskInterface */
        return $this->objectManager->create($taskClass);
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getRegisteredTasks(): array
    {
        return array_keys(self::SERVER_TASKS_CLASS);
    }

    // ---------------------------------------

    /**
     * @return \DateTime|null
     * @throws \Exception
     */
    private function getLastUpdateDate(): ?\DateTime
    {
        $lastUpdateDate = $this->registryManager->getValue('/servicing/last_update_time/');

        if ($lastUpdateDate !== null) {
            $lastUpdateDate = \M2E\Core\Helper\Date::createDateGmt($lastUpdateDate);
        }

        return $lastUpdateDate;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function setLastUpdateDateTime(): void
    {
        $this->registryManager->setValue(
            '/servicing/last_update_time/',
            \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'),
        );
    }
}
