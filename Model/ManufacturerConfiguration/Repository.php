<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ManufacturerConfiguration;

use M2E\TikTokShop\Model\ResourceModel\ManufacturerConfiguration as ManufacturerConfigurationResource;
use M2E\TikTokShop\Model\ResourceModel\ManufacturerConfiguration\CollectionFactory;

class Repository
{
    private ManufacturerConfigurationResource $manufacturerConfigurationResource;
    private CollectionFactory $manufacturerConfigurationCollectionFactory;

    public function __construct(
        ManufacturerConfigurationResource $manufacturerConfigurationResource,
        CollectionFactory $manufacturerConfigurationCollectionFactory
    ) {
        $this->manufacturerConfigurationResource = $manufacturerConfigurationResource;
        $this->manufacturerConfigurationCollectionFactory = $manufacturerConfigurationCollectionFactory;
    }

    public function create(
        \M2E\TikTokShop\Model\ManufacturerConfiguration $manufacturerConfiguration
    ): \M2E\TikTokShop\Model\ManufacturerConfiguration {
        $this->manufacturerConfigurationResource->save($manufacturerConfiguration);

        return $manufacturerConfiguration;
    }

    public function save(
        \M2E\TikTokShop\Model\ManufacturerConfiguration $manufacturerConfiguration
    ): \M2E\TikTokShop\Model\ManufacturerConfiguration {
        $this->manufacturerConfigurationResource->save($manufacturerConfiguration);

        return $manufacturerConfiguration;
    }

    public function delete(\M2E\TikTokShop\Model\ManufacturerConfiguration $config): void
    {
        $this->manufacturerConfigurationResource->delete($config);
    }

    public function find(int $id): ?\M2E\TikTokShop\Model\ManufacturerConfiguration
    {
        $collection = $this->manufacturerConfigurationCollectionFactory->create();
        $collection->addFieldToFilter(
            ManufacturerConfigurationResource::COLUMN_ID,
            ['eq' => $id]
        );

        $result = $collection->getFirstItem();
        if ($result->isObjectNew()) {
            return null;
        }

        return $result;
    }

    public function findByTitle(string $title): ?\M2E\TikTokShop\Model\ManufacturerConfiguration
    {
        $collection = $this->manufacturerConfigurationCollectionFactory->create();
        $collection->getSelect()->where(
            sprintf(
                'LOWER(%s) = LOWER(%s)',
                ManufacturerConfigurationResource::COLUMN_TITLE,
                $collection->getConnection()->quote(trim($title))
            )
        );

        $result = $collection->getFirstItem();
        if ($result->isObjectNew()) {
            return null;
        }

        return $result;
    }

    /**
     * @return \M2E\TikTokShop\Model\ManufacturerConfiguration[]
     */
    public function findAllByAccountId(int $accountId): array
    {
        $collection = $this->manufacturerConfigurationCollectionFactory->create();
        $collection->addFieldToFilter(
            ManufacturerConfigurationResource::COLUMN_ACCOUNT_ID,
            ['eq' => $accountId]
        );

        return array_values($collection->getItems());
    }

    public function get(int $id): \M2E\TikTokShop\Model\ManufacturerConfiguration
    {
        $manufacturerConfiguration = $this->find($id);
        if ($manufacturerConfiguration === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Manufacture Configuration not found');
        }

        return $manufacturerConfiguration;
    }

    public function isUniqueTitle(string $title, int $accountId, ?int $id = null): bool
    {
        $collection = $this->manufacturerConfigurationCollectionFactory->create();
        $collection->addFieldToFilter(
            ManufacturerConfigurationResource::COLUMN_TITLE,
            ['eq' => $title]
        );
        $collection->addFieldToFilter(
            ManufacturerConfigurationResource::COLUMN_ACCOUNT_ID,
            ['eq' => $accountId]
        );

        if ($id !== null) {
            $collection->addFieldToFilter(
                ManufacturerConfigurationResource::COLUMN_ID,
                ['neq' => $id]
            );
        }

        return $collection->getSize() === 0;
    }
}
