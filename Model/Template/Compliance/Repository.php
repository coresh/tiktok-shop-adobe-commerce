<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template\Compliance;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Template\Compliance $resource;
    private \M2E\TikTokShop\Model\Template\ComplianceFactory $complianceFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Template\Compliance\CollectionFactory $collectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Template\Compliance $resource,
        \M2E\TikTokShop\Model\ResourceModel\Template\Compliance\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Model\Template\ComplianceFactory $complianceFactory
    ) {
        $this->resource = $resource;
        $this->complianceFactory = $complianceFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function find(int $id): ?\M2E\TikTokShop\Model\Template\Compliance
    {
        $model = $this->complianceFactory->createEmpty();
        $this->resource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        return $model;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function get(int $id): \M2E\TikTokShop\Model\Template\Compliance
    {
        $template = $this->find($id);
        if ($template === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Compliance not found');
        }

        return $template;
    }

    public function delete(\M2E\TikTokShop\Model\Template\Compliance $template): void
    {
        $this->resource->delete($template);
    }

    public function create(\M2E\TikTokShop\Model\Template\Compliance $template): void
    {
        $this->resource->save($template);
    }

    public function save(\M2E\TikTokShop\Model\Template\Compliance $template): void
    {
        $this->resource->save($template);
    }

    /**
     * @return \M2E\TikTokShop\Model\Template\Compliance[]
     */
    public function getAll(): array
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }

    public function removeByAccountId(int $accountId): void
    {
        $deleteCondition = sprintf(
            '%s = ?',
            \M2E\TikTokShop\Model\ResourceModel\Template\Compliance::COLUMN_ACCOUNT_ID
        );

        $this->resource
            ->getConnection()
            ->delete($this->resource->getMainTable(), [$deleteCondition => $accountId]);
    }
}
