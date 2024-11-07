<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\HealthStatus\Task\Database\MysqlInfo;

use M2E\TikTokShop\Model\HealthStatus\Task\IssueType;
use M2E\TikTokShop\Model\HealthStatus\Task\Result as TaskResult;

class CrashedTables extends IssueType
{
    /** @var \M2E\TikTokShop\Model\HealthStatus\Task\Result\Factory */
    private $resultFactory;
    private \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper;

    public function __construct(
        \M2E\TikTokShop\Model\HealthStatus\Task\Result\Factory $resultFactory,
        \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper
    ) {
        parent::__construct();
        $this->resultFactory = $resultFactory;
        $this->dbStructureHelper = $dbStructureHelper;
    }

    public function process(): TaskResult
    {
        $crashedTables = [];
        foreach ($this->dbStructureHelper->getModuleTables() as $tableName) {
            if (!$this->dbStructureHelper->isTableStatusOk($tableName)) {
                $crashedTables[] = $tableName;
            }
        }

        $result = $this->resultFactory->create($this);
        $result->setTaskData($crashedTables)
               ->setTaskMessage($this->getTaskMessage($crashedTables));

        $result->setTaskResult(empty($crashedTables) ? TaskResult::STATE_SUCCESS : TaskResult::STATE_CRITICAL);

        return $result;
    }

    private function getTaskMessage(array $crashedTables): string
    {
        if (empty($crashedTables)) {
            return '';
        }

        return implode(', ', $crashedTables);
    }
}
