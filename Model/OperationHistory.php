<?php

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Helper\Data as Helper;
use M2E\TikTokShop\Model\ResourceModel\OperationHistory as OperationHistoryResource;

class OperationHistory extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    private ?self $object = null;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;
    private \M2E\TikTokShop\Model\OperationHistoryFactory $operationHistoryFactory;
    private \M2E\TikTokShop\Model\OperationHistory\Repository $repository;

    public function __construct(
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper,
        OperationHistoryFactory $operationHistoryFactory,
        \M2E\TikTokShop\Model\OperationHistory\Repository $repository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);

        $this->exceptionHelper = $exceptionHelper;
        $this->operationHistoryFactory = $operationHistoryFactory;
        $this->repository = $repository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(OperationHistoryResource::class);
    }

    // ----------------------------------------

    /**
     * @param self|string|int $value
     */
    public function setObject($value): self
    {
        if (is_object($value)) {
            $this->object = $value;
        } else {
            $object = $this->repository->find($value);
            if ($object === null) {
                $this->object = null;
            } else {
                $this->object = $object;
            }
        }

        return $this;
    }

    public function getObject(): ?self
    {
        return $this->object;
    }

    public function getParentObject(string $nick = null): ?self
    {
        if ($this->getObject()->getData(OperationHistoryResource::COLUMN_PARENT_ID) === null) {
            return null;
        }

        $parentId = (int)$this->getObject()->getData(OperationHistoryResource::COLUMN_PARENT_ID);
        $parentObject = $this->repository->get($parentId);

        if ($nick === null) {
            return $parentObject;
        }

        while ($parentObject->getData(OperationHistoryResource::COLUMN_NICK) != $nick) {
            $parentId = $parentObject->getData(OperationHistoryResource::COLUMN_PARENT_ID);
            if ($parentId === null) {
                return null;
            }

            $parentObject = $this->repository->get($parentId);
        }

        return $parentObject;
    }

    public function start(string $nick, ?int $parentId, int $initiator, array $data = []): bool
    {
        $this->object = $this->operationHistoryFactory->create();
        $this->object->setData(OperationHistoryResource::COLUMN_NICK, $nick);
        $this->object->setData(OperationHistoryResource::COLUMN_PARENT_ID, $parentId);
        $this->object->setData(OperationHistoryResource::COLUMN_DATA, json_encode($data));
        $this->object->setData(OperationHistoryResource::COLUMN_INITIATOR, $initiator);
        $this->object->setData(
            OperationHistoryResource::COLUMN_START_DATE,
            \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')
        );

        $this->repository->create($this->object);

        return true;
    }

    /**
     * @throws \Exception
     */
    public function stop(): bool
    {
        if (
            $this->object === null
            || $this->object->getData(OperationHistoryResource::COLUMN_END_DATE)
        ) {
            return false;
        }

        $this->object->setData(
            OperationHistoryResource::COLUMN_END_DATE,
            \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')
        );

        $this->repository->save($this->object);

        return true;
    }

    public function setContentData(string $key, $value): bool
    {
        if ($this->object === null) {
            return false;
        }

        $data = [];
        $existValue = $this->object->getData(OperationHistoryResource::COLUMN_DATA);
        if (!empty($existValue)) {
            $data = (array)json_decode($this->object->getData(OperationHistoryResource::COLUMN_DATA), true);
        }

        $data[$key] = $value;
        $this->object->setData(
            OperationHistoryResource::COLUMN_DATA,
            json_encode($data)
        );

        $this->repository->save($this->object);

        return true;
    }

    public function addContentData(string $key, $value): bool
    {
        $existedData = $this->getContentData($key);

        if ($existedData === null) {
            is_array($value) ? $existedData = [$value] : $existedData = $value;

            return $this->setContentData($key, $existedData);
        }

        if (is_array($existedData)) {
            $existedData[] = $value;
        } else {
            $existedData .= $value;
        }

        return $this->setContentData($key, $existedData);
    }

    public function getContentData(string $key)
    {
        if ($this->object === null) {
            return null;
        }

        $value = $this->object->getData(OperationHistoryResource::COLUMN_DATA);
        if (empty($value)) {
            return null;
        }

        $data = (array)json_decode($value, true);

        if (isset($data[$key])) {
            return $data[$key];
        }

        return null;
    }

    public function makeShutdownFunction(): bool
    {
        if ($this->object === null) {
            return false;
        }

        $objectId = $this->object->getId();
        register_shutdown_function(function () use ($objectId) {
            $error = error_get_last();
            if ($error === null || !in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
                return;
            }

            $object = $this->operationHistoryFactory->create();
            $object->setObject($objectId);

            if (!$object->stop()) {
                return;
            }

            $collection = $object
                ->getCollection()
                ->addFieldToFilter(OperationHistoryResource::COLUMN_PARENT_ID, $objectId);

            if ($collection->getSize()) {
                return;
            }

            $stackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $object->setContentData('fatal_error', [
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'trace' => $this->exceptionHelper->getFatalStackTraceInfo($stackTrace),
            ]);
        });

        return true;
    }

    //########################################

    public function getDataInfo($nestingLevel = 0)
    {
        if ($this->object === null) {
            return null;
        }

        $offset = str_repeat(' ', $nestingLevel * 7);
        $separationLine = str_repeat('#', 80 - strlen($offset));

        $nick = strtoupper($this->getObject()->getData(OperationHistoryResource::COLUMN_NICK));

        $contentData = (array)\M2E\TikTokShop\Helper\Json::decode(
            $this->getObject()->getData(OperationHistoryResource::COLUMN_DATA)
        );
        $contentData = preg_replace(
            '/^/m',
            "{$offset}",
            print_r($contentData, true)
        );

        return <<<INFO
{$offset}{$nick}
{$offset}Start Date: {$this->getObject()->getData(OperationHistoryResource::COLUMN_START_DATE)}
{$offset}End Date: {$this->getObject()->getData(OperationHistoryResource::COLUMN_END_DATE)}
{$offset}Total Time: {$this->getTotalTime()}

{$offset}{$separationLine}
{$contentData}
{$offset}{$separationLine}

INFO;
    }

    public function getFullDataInfo($nestingLevel = 0): ?string
    {
        if ($this->object === null) {
            return null;
        }

        $dataInfo = $this->getDataInfo($nestingLevel);

        $childObjects = $this
            ->getCollection()
            ->addFieldToFilter(OperationHistoryResource::COLUMN_PARENT_ID, $this->getObject()->getId())
            ->setOrder(OperationHistoryResource::COLUMN_START_DATE, 'ASC');

        $childObjects->getSize() > 0 && $nestingLevel++;

        foreach ($childObjects as $item) {
            $object = $this->operationHistoryFactory->create();
            $object->setObject($item);

            $dataInfo .= $object->getFullDataInfo($nestingLevel);
        }

        return $dataInfo;
    }

    // ---------------------------------------

    public function getExecutionInfo($nestingLevel = 0): ?string
    {
        if ($this->object === null) {
            return null;
        }

        $offset = str_repeat(' ', $nestingLevel * 5);

        $nick = $this->getObject()->getData(OperationHistoryResource::COLUMN_NICK);

        return <<<INFO
{$offset}<b>$nick ## {$this->getObject()->getId()}</b>
{$offset}start date: {$this->getObject()->getData(OperationHistoryResource::COLUMN_START_DATE)}
{$offset}end date:   {$this->getObject()->getData(OperationHistoryResource::COLUMN_END_DATE)}
{$offset}total time: {$this->getTotalTime()}
<br>
INFO;
    }

    public function getExecutionTreeUpInfo(): ?string
    {
        if ($this->object === null) {
            return null;
        }

        $extraParent = $this->getObject();
        $executionTree[] = $extraParent;

        while ($parentId = $extraParent->getData(OperationHistoryResource::COLUMN_PARENT_ID)) {
            $extraParent = $this->repository->get($parentId);
            $executionTree[] = $extraParent;
        }

        $info = '';
        $executionTree = array_reverse($executionTree);

        foreach ($executionTree as $nestingLevel => $item) {
            $object = $this->operationHistoryFactory->create();
            $object->setObject($item);

            $info .= $object->getExecutionInfo($nestingLevel);
        }

        return $info;
    }

    public function getExecutionTreeDownInfo($nestingLevel = 0): ?string
    {
        if ($this->object === null) {
            return null;
        }

        $info = $this->getExecutionInfo($nestingLevel);

        $childObjects = $this
            ->getCollection()
            ->addFieldToFilter(OperationHistoryResource::COLUMN_PARENT_ID, $this->getObject()->getId())
            ->setOrder(OperationHistoryResource::COLUMN_START_DATE, 'ASC');

        if ($childObjects->getSize() > 0) {
            $nestingLevel++;
        }

        foreach ($childObjects as $item) {
            $object = $this->operationHistoryFactory->create();
            $object->setObject($item);

            $info .= $object->getExecutionTreeDownInfo($nestingLevel);
        }

        return $info;
    }

    // ---------------------------------------

    protected function getTotalTime(): string
    {
        $endDateTimestamp = \M2E\Core\Helper\Date::createDateGmt(
            $this->getObject()->getData(OperationHistoryResource::COLUMN_END_DATE)
        )->getTimestamp();

        $startDateTimestamp = \M2E\Core\Helper\Date::createDateGmt(
            $this->getObject()->getData(OperationHistoryResource::COLUMN_START_DATE)
        )->getTimestamp();

        $totalTime = $endDateTimestamp - $startDateTimestamp;

        if ($totalTime < 0) {
            return 'n/a';
        }

        $minutes = (int)($totalTime / 60);
        if ($minutes < 10) {
            $minutes = '0' . $minutes;
        }

        $seconds = $totalTime - (int)$minutes * 60;
        if ($seconds < 10) {
            $seconds = '0' . $seconds;
        }

        return "$minutes:$seconds";
    }
}
