<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Install;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Template\Description as DescriptionResource;
use M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat as SellingFormatResource;
use M2E\TikTokShop\Model\ResourceModel\Template\Synchronization as SynchronizationResource;
use Magento\Framework\DB\Ddl\Table;

class PolicyHandler implements \M2E\TikTokShop\Model\Setup\InstallHandlerInterface
{
    private \M2E\TikTokShop\Helper\Module\Database\Tables $tablesHelper;

    public function __construct(TablesHelper $tablesHelper)
    {
        $this->tablesHelper = $tablesHelper;
    }

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installTemplateSellingFormatTable($setup);
        $this->installTemplateSynchronizationTable($setup);
        $this->installTemplateDescriptionTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installTemplateSellingFormatTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_TEMPLATE_SELLING_FORMAT);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                SellingFormatResource::COLUMN_ID,
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
                SellingFormatResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_IS_CUSTOM_TEMPLATE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_CUSTOM_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_CUSTOM_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_PERCENTAGE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 100]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_MODIFICATION_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_MIN_POSTED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_QTY_MAX_POSTED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_FIXED_PRICE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_FIXED_PRICE_MODIFIER,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_FIXED_PRICE_CUSTOM_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_CASH_ON_DELIVERY,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                SellingFormatResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('is_custom_template', SellingFormatResource::COLUMN_IS_CUSTOM_TEMPLATE)
            ->addIndex('title', SellingFormatResource::COLUMN_TITLE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installTemplateSynchronizationTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_TEMPLATE_SYNCHRONIZATION);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                SynchronizationResource::COLUMN_ID,
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
                SynchronizationResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_IS_CUSTOM_TEMPLATE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_STATUS_ENABLED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_IS_IN_STOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_QTY_CALCULATED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_QTY_CALCULATED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_ADVANCED_RULES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_LIST_ADVANCED_RULES_FILTERS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_QTY,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_QTY_MAX_APPLIED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_PRICE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_TITLE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_CATEGORIES,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_IMAGES,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_DESCRIPTION,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_REVISE_UPDATE_OTHER,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_FILTER_USER_LOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_STATUS_ENABLED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_IS_IN_STOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_QTY_CALCULATED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_QTY_CALCULATED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_ADVANCED_RULES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_RELIST_ADVANCED_RULES_FILTERS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_STATUS_DISABLED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_OUT_OFF_STOCK,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_QTY_CALCULATED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_QTY_CALCULATED_VALUE,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_ADVANCED_RULES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_STOP_ADVANCED_RULES_FILTERS,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                SynchronizationResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'title',
                SynchronizationResource::COLUMN_TITLE
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installTemplateDescriptionTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_TEMPLATE_DESCRIPTION);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                DescriptionResource::COLUMN_ID,
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
                DescriptionResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_IS_CUSTOM_TEMPLATE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_TITLE_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_TITLE_TEMPLATE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_DESCRIPTION_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_DESCRIPTION_TEMPLATE,
                Table::TYPE_TEXT,
                \M2E\TikTokShop\Model\Setup\Installer::LONG_COLUMN_SIZE,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_IMAGE_MAIN_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_IMAGE_MAIN_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_IMAGES_MODE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_IMAGES_LIMIT,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                DescriptionResource::COLUMN_GALLERY_IMAGES_ATTRIBUTE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                DescriptionResource::COLUMN_RESIZE_IMAGE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                DescriptionResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                DescriptionResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('is_custom_template', DescriptionResource::COLUMN_IS_CUSTOM_TEMPLATE)
            ->addIndex('title', DescriptionResource::COLUMN_TITLE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }
}
