<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Install;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Instruction as ProductInstructionResource;
use M2E\TikTokShop\Model\ResourceModel\Listing\Other as ListingOtherResource;
use M2E\TikTokShop\Model\ResourceModel\Product as ListingProductResource;
use M2E\TikTokShop\Model\ResourceModel\Product\VariantSku as ListingProductVariantResource;
use M2E\TikTokShop\Model\ResourceModel\ScheduledAction as ScheduledActionResource;
use M2E\TikTokShop\Model\ResourceModel\StopQueue as StopQueueResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class ProductHandler implements \M2E\TikTokShop\Model\Setup\InstallHandlerInterface
{
    private \M2E\TikTokShop\Helper\Module\Database\Tables $tablesHelper;

    public function __construct(TablesHelper $tablesHelper)
    {
        $this->tablesHelper = $tablesHelper;
    }

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installProductTable($setup);
        $this->installProductVariantSkuTable($setup);
        $this->installProductInstructionTable($setup);
        $this->installProductScheduledActionTable($setup);
        $this->installImageTable($setup);
        $this->installProductImageRelationTable($setup);
        $this->installStopQueueTable($setup);
        $this->installListingOtherTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installProductTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_PRODUCT);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                ListingProductResource::COLUMN_ID,
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
                ListingProductResource::COLUMN_LISTING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingProductResource::COLUMN_TTS_PRODUCT_ID,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                ListingProductResource::COLUMN_IS_SIMPLE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                ListingProductResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingProductResource::COLUMN_STATUS_CHANGER,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_TITLE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_DESCRIPTION,
                Table::TYPE_TEXT,
                40,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_BRAND_ID,
                Table::TYPE_TEXT,
                30
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_BRAND_NAME,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_MIN_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_MAX_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_CATEGORY,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_CATEGORIES_DATA,
                Table::TYPE_TEXT,
                \M2E\TikTokShop\Model\Setup\Installer::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                \M2E\TikTokShop\Model\Setup\Installer::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('listing_id', ListingProductResource::COLUMN_LISTING_ID)
            ->addIndex('magento_product_id', ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addIndex('status', ListingProductResource::COLUMN_STATUS)
            ->addIndex('status_changer', ListingProductResource::COLUMN_STATUS_CHANGER)
            ->addIndex('online_category', ListingProductResource::COLUMN_ONLINE_CATEGORY)
            ->addIndex('online_title', ListingProductResource::COLUMN_ONLINE_TITLE)
            ->addIndex('template_category_id', ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installProductVariantSkuTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_PRODUCT_VARIANT_SKU);

        $listingProductVariantTable = $setup->getConnection()->newTable($tableName);

        $listingProductVariantTable
            ->addColumn(
                ListingProductVariantResource::COLUMN_ID,
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
                ListingProductVariantResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_SKU_ID,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_WAREHOUSE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_ONLINE_SKU,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_ONLINE_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_ONLINE_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_ONLINE_IMAGE,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_ONLINE_IDENTIFIER_ID,
                Table::TYPE_TEXT,
                50,
                ['default' => null]
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_ONLINE_IDENTIFIER_TYPE,
                Table::TYPE_TEXT,
                10,
                ['default' => null]
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('product_id', ListingProductVariantResource::COLUMN_PRODUCT_ID)
            ->addIndex('magento_product_id', ListingProductVariantResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addIndex('sku_id', ListingProductVariantResource::COLUMN_SKU_ID)
            ->addIndex('warehouse_id', ListingProductVariantResource::COLUMN_WAREHOUSE_ID)
            ->addIndex('online_qty', ListingProductVariantResource::COLUMN_ONLINE_QTY)
            ->addIndex('online_price', ListingProductVariantResource::COLUMN_ONLINE_PRICE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($listingProductVariantTable);
    }

    private function installProductInstructionTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_PRODUCT_INSTRUCTION);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                ProductInstructionResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_INITIATOR,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_PRIORITY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductInstructionResource::COLUMN_SKIP_UNTIL,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('listing_product_id', ProductInstructionResource::COLUMN_LISTING_PRODUCT_ID)
            ->addIndex('type', ProductInstructionResource::COLUMN_TYPE)
            ->addIndex('priority', ProductInstructionResource::COLUMN_PRIORITY)
            ->addIndex('skip_until', ProductInstructionResource::COLUMN_SKIP_UNTIL)
            ->addIndex('create_date', ProductInstructionResource::COLUMN_CREATE_DATE)
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installProductScheduledActionTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_PRODUCT_SCHEDULED_ACTION);

        $productScheduledAction = $setup
            ->getConnection()
            ->newTable($tableName);

        $productScheduledAction
            ->addColumn(
                ScheduledActionResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_ACTION_TYPE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_STATUS_CHANGER,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_IS_FORCE,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_TAG,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_VARIANTS_SETTINGS,
                Table::TYPE_TEXT,
                \M2E\TikTokShop\Model\Setup\Installer::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                \M2E\TikTokShop\Model\Setup\Installer::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex(
                'listing_product_id',
                [ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex('action_type', ScheduledActionResource::COLUMN_ACTION_TYPE)
            ->addIndex('tag', ScheduledActionResource::COLUMN_TAG)
            ->addIndex('create_date', ScheduledActionResource::COLUMN_CREATE_DATE)
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($productScheduledAction);
    }

    private function installImageTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_IMAGE);

        $table = $setup->getConnection()->newTable($tableName);

        $table->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            ]
        );
        $table->addColumn(
            'type',
            Table::TYPE_TEXT,
            50,
            ['nullable' => false]
        );
        $table->addColumn(
            'hash',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $table->addColumn(
            'uri',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $table->addColumn(
            'url',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false]
        );
        $table->addColumn(
            'update_date',
            Table::TYPE_DATETIME
        );
        $table->addColumn(
            'create_date',
            Table::TYPE_DATETIME
        );
        $table->addIndex(
            'hash__type',
            [
                \M2E\TikTokShop\Model\ResourceModel\Image::COLUMN_HASH,
                \M2E\TikTokShop\Model\ResourceModel\Image::COLUMN_TYPE,
            ],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );

        $setup->getConnection()->createTable($table);
    }

    private function installProductImageRelationTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_PRODUCT_IMAGE_RELATION);

        $table = $setup->getConnection()->newTable($tableName);

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
                'listing_product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false,]
            )
            ->addColumn(
                'image_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false,]
            )
            ->addIndex('listing_product_id', 'listing_product_id');

        $setup->getConnection()->createTable($table);
    }

    private function installStopQueueTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_STOP_QUEUE);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                StopQueueResource::COLUMN_ID,
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
                StopQueueResource::COLUMN_IS_PROCESSED,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                StopQueueResource::COLUMN_REQUEST_DATA,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                StopQueueResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                StopQueueResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('is_processed', StopQueueResource::COLUMN_IS_PROCESSED)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installListingOtherTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $tableName = $this->tablesHelper->getFullName(TablesHelper::TABLE_NAME_LISTING_OTHER);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                ListingOtherResource::COLUMN_ID,
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
                ListingOtherResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_SHOP_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_WAREHOUSE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_TTS_PRODUCT_ID,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_MOVED_TO_LISTING_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_SKU_ID,
                Table::TYPE_TEXT,
                50,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_SKU,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_CURRENCY,
                Table::TYPE_TEXT,
                10,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                ListingOtherResource::COLUMN_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_INVENTORY_DATA,
                Table::TYPE_TEXT,
                \M2E\TikTokShop\Model\Setup\Installer::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_CATEGORY_ID,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_CATEGORIES_DATA,
                Table::TYPE_TEXT,
                \M2E\TikTokShop\Model\Setup\Installer::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_IDENTIFIER_ID,
                Table::TYPE_TEXT,
                50,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_IDENTIFIER_TYPE,
                Table::TYPE_TEXT,
                10,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ListingOtherResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('account_id', ListingOtherResource::COLUMN_ACCOUNT_ID)
            ->addIndex('shop_id', ListingOtherResource::COLUMN_SHOP_ID)
            ->addIndex('tts_product_id', ListingOtherResource::COLUMN_TTS_PRODUCT_ID)
            ->addIndex('sku_id', ListingOtherResource::COLUMN_SKU_ID)
            ->addIndex('magento_product_id', ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addIndex('status', ListingOtherResource::COLUMN_STATUS)
            ->addIndex('title', ListingOtherResource::COLUMN_TITLE)
            ->addIndex('sku', ListingOtherResource::COLUMN_SKU)
            ->addIndex('currency', ListingOtherResource::COLUMN_CURRENCY)
            ->addIndex('price', ListingOtherResource::COLUMN_PRICE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }
}
