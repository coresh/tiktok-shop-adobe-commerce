<?php

namespace M2E\TikTokShop\Model\HealthStatus\Task;

/**
 * Class \M2E\TikTokShop\Model\HealthStatus\Task\Result
 */
class Result extends \M2E\TikTokShop\Model\AbstractModel
{
    public const STATE_CRITICAL = 40;
    public const STATE_WARNING = 30;
    public const STATE_NOTICE = 20;
    public const STATE_SUCCESS = 10;

    private $taskHash;
    private $taskType;
    private $taskMustBeShownIfSuccess;

    private $tabName;
    private $fieldSetName;
    private $fieldName;

    private $taskResult = self::STATE_SUCCESS;
    private $taskMessage = '';
    private $taskData = [];

    public function __construct(
        $taskHash,
        $taskType,
        $taskMustBeShownIfSuccess,
        $tabName,
        $fieldSetName,
        $fieldName,
        array $data = []
    ) {
        parent::__construct($data);

        $this->taskHash = $taskHash;
        $this->taskType = $taskType;
        $this->taskMustBeShownIfSuccess = $taskMustBeShownIfSuccess;

        $this->tabName = $tabName;
        $this->fieldSetName = $fieldSetName;
        $this->fieldName = $fieldName;
    }

    //########################################

    public function getTaskHash()
    {
        return $this->taskHash;
    }

    public function getTaskType()
    {
        return $this->taskType;
    }

    public function isTaskMustBeShowIfSuccess()
    {
        return $this->taskMustBeShownIfSuccess;
    }

    //----------------------------------------

    public function getTabName()
    {
        return $this->tabName;
    }

    public function getFieldSetName()
    {
        return $this->fieldSetName;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    //----------------------------------------

    public function setTaskResult($value)
    {
        $this->taskResult = $value;

        return $this;
    }

    public function getTaskResult()
    {
        return $this->taskResult;
    }

    //----------------------------------------

    public function setTaskMessage($message)
    {
        $this->taskMessage = $message;

        return $this;
    }

    public function getTaskMessage()
    {
        return $this->taskMessage;
    }

    //----------------------------------------

    public function setTaskData($data)
    {
        $this->taskData = $data;

        return $this;
    }

    public function getTaskData()
    {
        return $this->taskData;
    }

    //########################################

    public function isCritical()
    {
        return $this->getTaskResult() == self::STATE_CRITICAL;
    }

    public function isWaring()
    {
        return $this->getTaskResult() == self::STATE_WARNING;
    }

    public function isNotice()
    {
        return $this->getTaskResult() == self::STATE_NOTICE;
    }

    public function isSuccess()
    {
        return $this->getTaskResult() == self::STATE_SUCCESS;
    }

    //----------------------------------------

    public function isTaskTypeIssue()
    {
        return $this->getTaskType() == IssueType::TYPE;
    }

    public function isTaskTypeInfo()
    {
        return $this->getTaskType() == InfoType::TYPE;
    }

    //########################################
}
