<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m11;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use Magento\Framework\DB\Ddl\Table;
use M2E\TikTokShop\Model\ResourceModel\Listing as ListingResource;
use M2E\TikTokShop\Model\ResourceModel\Template\Synchronization as SyncResource;

class AddCompliancePolicy extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createTableTemplateCompliance();
        $this->modifyListing();
        $this->modifySyncTemplate();
        $this->modifyProduct();
    }

    private function createTableTemplateCompliance(): void
    {
        $table = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::PREFIX . 'template_compliance'));

        $table
            ->addColumn(
                'id',
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
                'account_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'manufacturer_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'responsible_person_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($table);
    }

    private function modifyListing(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_LISTING);
        $modifier->addColumn(
            'template_compliance_id',
            'INT UNSIGNED',
            'NULL',
            ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID,
            true,
            false
        );

        $modifier->commit();
    }

    private function modifySyncTemplate(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_TEMPLATE_SYNCHRONIZATION);
        $modifier->addColumn(
            SyncResource::COLUMN_REVISE_UPDATE_COMPLIANCE,
            'SMALLINT UNSIGNED NOT NULL',
            null,
            SyncResource::COLUMN_REVISE_UPDATE_DESCRIPTION,
            false,
            false
        );

        $modifier->commit();
    }

    private function modifyProduct(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_PRODUCT);
        $modifier
            ->addColumn(
                \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_ONLINE_MANUFACTURER_ID,
                'VARCHAR(255)',
                'NULL',
                null,
                false,
                false
            )
            ->addColumn(
                'online_responsible_person_id',
                'VARCHAR(255)',
                'NULL',
                null,
                false,
                false
            );

        $modifier->commit();
    }
}
