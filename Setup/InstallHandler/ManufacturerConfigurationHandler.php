<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\InstallHandler;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\ManufacturerConfiguration as ManufacturerConfigurationResource;
use Magento\Framework\DB\Ddl\Table;

class ManufacturerConfigurationHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\TikTokShop\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installManufacturerConfiguration($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installManufacturerConfiguration(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_MANUFACTURER_CONFIGURATION));

        $table
            ->addColumn(
                ManufacturerConfigurationResource::COLUMN_ID,
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
                ManufacturerConfigurationResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true]
            )
            ->addColumn(
                ManufacturerConfigurationResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ManufacturerConfigurationResource::COLUMN_MANUFACTURER_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false,]
            )
            ->addColumn(
                ManufacturerConfigurationResource::COLUMN_RESPONSIBLE_PERSON_IDS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => false]
            )
            ->addColumn(
                ManufacturerConfigurationResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                ManufacturerConfigurationResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true]
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }
}
