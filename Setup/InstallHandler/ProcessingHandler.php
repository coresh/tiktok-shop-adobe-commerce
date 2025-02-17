<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\InstallHandler;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Processing as ProcessingResource;
use M2E\TikTokShop\Model\ResourceModel\Processing\Lock as ProcessingLockResource;
use M2E\TikTokShop\Model\ResourceModel\Processing\PartialData as PartialDataResource;
use Magento\Framework\DB\Ddl\Table;

class ProcessingHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\TikTokShop\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installProcessingTable($setup);
        $this->installProcessingPartialDataTable($setup);
        $this->installProcessingLockTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installProcessingTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PROCESSING);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                ProcessingResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                ProcessingResource::COLUMN_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ProcessingResource::COLUMN_SERVER_HASH,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProcessingResource::COLUMN_STAGE,
                Table::TYPE_TEXT,
                20,
                ['nullable' => false]
            )
            ->addColumn(
                ProcessingResource::COLUMN_HANDLER_NICK,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProcessingResource::COLUMN_PARAMS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ProcessingResource::COLUMN_RESULT_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ProcessingResource::COLUMN_RESULT_MESSAGES,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ProcessingResource::COLUMN_DATA_NEXT_PART,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true]
            )
            ->addColumn(
                ProcessingResource::COLUMN_IS_COMPLETED,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ProcessingResource::COLUMN_EXPIRATION_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                ProcessingResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ProcessingResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('type', ProcessingResource::COLUMN_TYPE)
            ->addIndex('stage', ProcessingResource::COLUMN_STAGE)
            ->addIndex('is_completed', ProcessingResource::COLUMN_IS_COMPLETED)
            ->addIndex('expiration_date', ProcessingResource::COLUMN_EXPIRATION_DATE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installProcessingPartialDataTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PROCESSING_PARTIAL_DATA);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                PartialDataResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                PartialDataResource::COLUMN_PROCESSING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                PartialDataResource::COLUMN_PART_NUMBER,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                PartialDataResource::COLUMN_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addIndex('part_number', PartialDataResource::COLUMN_PART_NUMBER)
            ->addIndex('processing_id', PartialDataResource::COLUMN_PROCESSING_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installProcessingLockTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PROCESSING_LOCK);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                ProcessingLockResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                ProcessingLockResource::COLUMN_PROCESSING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProcessingLockResource::COLUMN_OBJECT_NICK,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProcessingLockResource::COLUMN_OBJECT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProcessingLockResource::COLUMN_TAG,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ProcessingLockResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ProcessingLockResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('processing_id', ProcessingLockResource::COLUMN_PROCESSING_ID)
            ->addIndex('object_nick', ProcessingLockResource::COLUMN_OBJECT_NICK)
            ->addIndex('object_id', ProcessingLockResource::COLUMN_OBJECT_ID)
            ->addIndex('tag', ProcessingLockResource::COLUMN_TAG)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }
}
