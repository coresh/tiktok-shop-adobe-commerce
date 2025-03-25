<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m03;

use M2E\TikTokShop\Helper\Module\Database\Tables;
use M2E\TikTokShop\Model\ResourceModel\ManufacturerConfiguration as ManufacturerConfigurationResource;
use Magento\Framework\DB\Ddl\Table;

class AddManufacturerConfiguration extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createTable();
        $this->addColumnToProduct();
        $this->migrateTemplateComplianceData();
        $this->dropColumnTemplateComplianceIdFromListing();
        $this->dropTemplateComplianceTable();
    }

    private function createTable(): void
    {
        $table = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_MANUFACTURER_CONFIGURATION));

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

        $this->getConnection()->createTable($table);
    }

    private function addColumnToProduct(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);
        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_MANUFACTURER_CONFIG_ID,
            'SMALLINT UNSIGNED',
            null,
            null,
            false,
            false
        );
        $modifier->commit();
    }

    private function migrateTemplateComplianceData(): void
    {
        $select = $this
            ->getConnection()
            ->select()
            ->from($this->getFullTableName(Tables::PREFIX . 'template_compliance'));

        $stmt = $select->query();

        $insertData = [];
        while ($row = $stmt->fetch()) {
            $insertData[] = [
                ManufacturerConfigurationResource::COLUMN_ACCOUNT_ID => $row['account_id'],
                ManufacturerConfigurationResource::COLUMN_TITLE => trim($row['title']),
                ManufacturerConfigurationResource::COLUMN_MANUFACTURER_ID => $row['manufacturer_id'],
                ManufacturerConfigurationResource::COLUMN_RESPONSIBLE_PERSON_IDS => $row['responsible_person_ids'],
                ManufacturerConfigurationResource::COLUMN_CREATE_DATE => $row['create_date'],
                ManufacturerConfigurationResource::COLUMN_UPDATE_DATE => $row['update_date'],
            ];
        }

        if (empty($insertData)) {
            return;
        }

        $this->getConnection()
             ->insertMultiple(
                 $this->getFullTableName(Tables::TABLE_NAME_MANUFACTURER_CONFIGURATION),
                 $insertData
             );
    }

    private function dropColumnTemplateComplianceIdFromListing(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_LISTING);
        $modifier->dropColumn('template_compliance_id', true, false);
        $modifier->commit();
    }

    private function dropTemplateComplianceTable(): void
    {
        $this->getConnection()
             ->dropTable($this->getFullTableName(Tables::PREFIX . 'template_compliance'));
    }
}
