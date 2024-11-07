<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\OperationHistory;

use M2E\TikTokShop\Model\ResourceModel\OperationHistory as OperationHistoryResource;

class Repository
{
    private OperationHistoryResource $resource;
    private \M2E\TikTokShop\Model\OperationHistoryFactory $operationHistoryFactory;

    public function __construct(
        OperationHistoryResource $resource,
        \M2E\TikTokShop\Model\OperationHistoryFactory $operationHistoryFactory
    ) {
        $this->resource = $resource;
        $this->operationHistoryFactory = $operationHistoryFactory;
    }

    public function find(int $id): ?\M2E\TikTokShop\Model\OperationHistory
    {
        $model = $this->operationHistoryFactory->create();
        $this->resource->load($model, $id);
        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    public function get(int $id): \M2E\TikTokShop\Model\OperationHistory
    {
        $model = $this->find($id);
        if ($model === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Entity not found by id ' . $id);
        }

        return $model;
    }

    public function clear(\DateTime $borderDate): void
    {
        $minDate = $borderDate->format('Y-m-d H:i:s');

        $this->resource->getConnection()->delete(
            $this->resource->getMainTable(),
            sprintf("%s <= '%s'", OperationHistoryResource::COLUMN_START_DATE, $minDate)
        );
    }

    public function create(\M2E\TikTokShop\Model\OperationHistory $object): void
    {
        $this->resource->save($object);
    }

    public function save(\M2E\TikTokShop\Model\OperationHistory $object): void
    {
        $this->resource->save($object);
    }
}
