<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Log\System;

use M2E\TikTokShop\Model\ResourceModel\Log\System as LogSystemResource;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Log\System $resource;
    private \M2E\TikTokShop\Model\ResourceModel\Log\System\CollectionFactory $collectionFactory;
    private \M2E\TikTokShop\Model\Log\SystemFactory $logSystemFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Log\SystemFactory $logSystemFactory,
        \M2E\TikTokShop\Model\ResourceModel\Log\System $resource,
        \M2E\TikTokShop\Model\ResourceModel\Log\System\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->logSystemFactory = $logSystemFactory;
    }

    public function create(int $type, string $class, string $message, string $details, array $additionalData = []): void
    {
        $log = $this->logSystemFactory->create();
        $log->init($type, $class, $message, $details, $additionalData);

        $this->resource->save($log);
    }

    public function findExceptionsCountByBackInterval(\DateTime $borderDate): int
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(
                LogSystemResource::COLUMN_TYPE,
                ['neq' => '\\' . \M2E\Core\Model\Exception\Connection::class],
            )
            ->addFieldToFilter(LogSystemResource::COLUMN_TYPE, ['nlike' => '%Logging%'])
            ->addFieldToFilter(
                LogSystemResource::COLUMN_CREATE_DATE,
                ['gt' => $borderDate->format('Y-m-d H:i:s')],
            );

        return (int)$collection->getSize();
    }

    public function isExistErrors(): bool
    {
        $collection = $this->collectionFactory->create();

        $collection
            ->addFieldToFilter(LogSystemResource::COLUMN_TYPE, ['gt' => \M2E\TikTokShop\Model\Log\System::TYPE_LOGGER])
            ->setPageSize(1);

        $item = $collection->getFirstItem();

        return !$item->isObjectNew();
    }

    public function clearByAmount(int $moreThan): void
    {
        $tableName = $this->resource->getMainTable();

        $connection = $this->resource->getConnection();

        $counts = (int)$connection
            ->select()
            ->from($tableName, [new \Zend_Db_Expr('COUNT(*)')])
            ->query()
            ->fetchColumn();

        if ($counts <= $moreThan) {
            return;
        }

        $ids = $connection
            ->select()
            ->from($tableName, 'id')
            ->limit($counts - $moreThan)
            ->order(['id ASC'])
            ->query()
            ->fetchAll(\Zend_Db::FETCH_COLUMN);

        $connection
            ->delete($tableName, 'id IN (' . implode(',', $ids) . ')');
    }

    public function clearByTime(\DateTime $borderDate): void
    {
        $minDate = $borderDate->format('Y-m-d 00:00:00');

        $this->resource->getConnection()->delete($this->resource->getMainTable(), "create_date < '$minDate'");
    }
}
