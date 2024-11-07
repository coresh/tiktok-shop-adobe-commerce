<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Setup;

use M2E\TikTokShop\Model\ResourceModel\Setup as SetupResource;
use Magento\Framework\DB\Ddl\Table;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Setup $setupResource;
    private \M2E\TikTokShop\Model\ResourceModel\Setup\CollectionFactory $collectionFactory;
    private \M2E\TikTokShop\Helper\Module\Database\Structure $dbHelper;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Setup $setupResource,
        \M2E\TikTokShop\Model\ResourceModel\Setup\CollectionFactory $collectionFactory,
        \M2E\TikTokShop\Helper\Module\Database\Structure $dbHelper
    ) {
        $this->setupResource = $setupResource;
        $this->collectionFactory = $collectionFactory;
        $this->dbHelper = $dbHelper;
    }

    public function create(?string $fromVersion, string $toVersion): \M2E\TikTokShop\Model\Setup
    {
        if (!$this->isSetupTableExists()) {
            $this->createTable();
        }

        $collection = $this->collectionFactory->create();
        if ($fromVersion === null) {
            $collection->addFieldToFilter(SetupResource::COLUMN_VERSION_FROM, ['null' => true]);
        } else {
            $collection->addFieldToFilter(SetupResource::COLUMN_VERSION_FROM, $fromVersion);
        }

        $collection->addFieldToFilter(SetupResource::COLUMN_VERSION_TO, $toVersion);
        $collection->getSelect()
                   ->limit(1);

        $setupObject = $collection->getFirstItem();

        if ($setupObject->isObjectNew()) {
            $setupObject->init($fromVersion, $toVersion);

            $this->setupResource->save($setupObject);
        }

        return $setupObject;
    }

    public function save(\M2E\TikTokShop\Model\Setup $setup): void
    {
        if (!$this->isSetupTableExists()) {
            return;
        }

        $this->setupResource->save($setup);
    }

    public function findLastExecuted(): ?\M2E\TikTokShop\Model\Setup
    {
        if (!$this->isSetupTableExists()) {
            return null;
        }

        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(SetupResource::COLUMN_IS_COMPLETED, 1);

        /** @var \M2E\TikTokShop\Model\Setup $maxCompletedItem */
        $maxCompletedItem = null;
        foreach ($collection->getItems() as $completedItem) {
            if ($maxCompletedItem === null) {
                $maxCompletedItem = $completedItem;
                continue;
            }

            if (version_compare($maxCompletedItem->getVersionTo(), $completedItem->getVersionTo(), '>')) {
                continue;
            }

            $maxCompletedItem = $completedItem;
        }

        return $maxCompletedItem;
    }

    /**
     * @return \M2E\TikTokShop\Model\Setup[]
     */
    public function findNotCompletedUpgrades(): array
    {
        if (!$this->isSetupTableExists()) {
            return [];
        }

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(SetupResource::COLUMN_VERSION_FROM, ['notnull' => true]);
        $collection->addFieldToFilter(SetupResource::COLUMN_VERSION_TO, ['notnull' => true]);
        $collection->addFieldToFilter(SetupResource::COLUMN_IS_COMPLETED, 0);

        return array_values($collection->getItems());
    }

    public function findLastUpgrade(): ?\M2E\TikTokShop\Model\Setup
    {
        if (!$this->isSetupTableExists()) {
            return null;
        }

        $collection = $this->collectionFactory->create();

        $setupObject = $collection->getLastItem();
        if ($setupObject->isObjectNew()) {
            return null;
        }

        return $setupObject;
    }

    // ----------------------------------------

    public function isAlreadyInstalled(): bool
    {
        if (!$this->isSetupTableExists()) {
            return false;
        }

        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(SetupResource::COLUMN_VERSION_FROM, ['null' => true])
            ->addFieldToFilter(SetupResource::COLUMN_IS_COMPLETED, 1);

        $item = $collection->getFirstItem();

        return !$item->isObjectNew();
    }

    public function createTable(): void
    {
        if ($this->isSetupTableExists()) {
            return;
        }

        $tableName = $this->dbHelper->getTableNameWithPrefix(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_SETUP,
        );

        $setupTable = $this->setupResource
            ->getConnection()
            ->newTable($tableName)
            ->addColumn(
                SetupResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ],
            )
            ->addColumn(
                SetupResource::COLUMN_VERSION_FROM,
                Table::TYPE_TEXT,
                32,
                ['default' => null],
            )
            ->addColumn(
                SetupResource::COLUMN_VERSION_TO,
                Table::TYPE_TEXT,
                32,
                ['default' => null],
            )
            ->addColumn(
                SetupResource::COLUMN_IS_COMPLETED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                SetupResource::COLUMN_PROFILER_DATA,
                Table::TYPE_TEXT,
                SetupResource::LONG_COLUMN_SIZE,
                ['default' => null],
            )
            ->addColumn(
                SetupResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                SetupResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addIndex('version_from', SetupResource::COLUMN_VERSION_FROM)
            ->addIndex('version_to', SetupResource::COLUMN_VERSION_TO)
            ->addIndex('is_completed', SetupResource::COLUMN_IS_COMPLETED)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci');

        $this->setupResource
            ->getConnection()
            ->createTable($setupTable);
    }

    private function isSetupTableExists(): bool
    {
        return $this->dbHelper->isTableExists(\M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_SETUP, true);
    }
}
