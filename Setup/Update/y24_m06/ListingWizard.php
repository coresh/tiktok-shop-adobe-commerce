<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m06;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Listing\Wizard as WizardResource;
use M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Product as ProductResource;
use M2E\TikTokShop\Model\ResourceModel\Listing\Wizard\Step as StepResource;
use Magento\Framework\DB\Ddl\Table;

class ListingWizard extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createWizardTable();
        $this->createStepTable();
        $this->createProductTable();
    }

    private function createWizardTable(): void
    {
        $listingWizardTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_WIZARD));

        $listingWizardTable
            ->addColumn(
                WizardResource::COLUMN_ID,
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
                WizardResource::COLUMN_LISTING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                WizardResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                50,
            )
            ->addColumn(
                WizardResource::COLUMN_CURRENT_STEP_NICK,
                Table::TYPE_TEXT,
                150,
            )
            ->addColumn(
                WizardResource::COLUMN_PRODUCT_COUNT_TOTAL,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                WizardResource::COLUMN_IS_COMPLETED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                WizardResource::COLUMN_PROCESS_START_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                WizardResource::COLUMN_PROCESS_END_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                WizardResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                WizardResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addIndex('listing_id', WizardResource::COLUMN_LISTING_ID)
            ->addIndex('is_completed', WizardResource::COLUMN_IS_COMPLETED)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($listingWizardTable);
    }

    private function createStepTable(): void
    {
        $stepTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_WIZARD_STEP));

        $stepTable
            ->addColumn(
                StepResource::COLUMN_ID,
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
                StepResource::COLUMN_WIZARD_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                StepResource::COLUMN_NICK,
                Table::TYPE_TEXT,
                150,
            )
            ->addColumn(
                StepResource::COLUMN_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null],
            )
            ->addColumn(
                StepResource::COLUMN_IS_COMPLETED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                StepResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                StepResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addIndex('wizard_id', StepResource::COLUMN_WIZARD_ID)
            ->addIndex('is_completed', StepResource::COLUMN_IS_COMPLETED)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($stepTable);
    }

    private function createProductTable(): void
    {
        $productTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_LISTING_WIZARD_PRODUCT));

        $productTable
            ->addColumn(
                ProductResource::COLUMN_ID,
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
                ProductResource::COLUMN_WIZARD_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ProductResource::COLUMN_UNMANAGED_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
            )
            ->addColumn(
                ProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ProductResource::COLUMN_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
            )
            ->addColumn(
                ProductResource::COLUMN_IS_PROCESSED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addIndex('wizard_id', ProductResource::COLUMN_WIZARD_ID)
            ->addIndex('category_id', ProductResource::COLUMN_CATEGORY_ID)
            ->addIndex('is_processed', ProductResource::COLUMN_IS_PROCESSED)
            ->addIndex(
                'wizard_id_magento_product_id',
                [ProductResource::COLUMN_WIZARD_ID, ProductResource::COLUMN_MAGENTO_PRODUCT_ID],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE],
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($productTable);
    }
}
