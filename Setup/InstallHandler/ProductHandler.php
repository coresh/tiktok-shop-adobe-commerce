<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\InstallHandler;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Instruction as ProductInstructionResource;
use M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct as UnmanagedProductResource;
use M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku as UnmanagedProductVariantResource;
use M2E\TikTokShop\Model\ResourceModel\Product as ListingProductResource;
use M2E\TikTokShop\Model\ResourceModel\Product\VariantSku as ListingProductVariantResource;
use M2E\TikTokShop\Model\ResourceModel\GlobalProduct as GlobalProductResource;
use M2E\TikTokShop\Model\ResourceModel\GlobalProduct\VariantSku as GlobalProductVariantSkuResource;
use M2E\TikTokShop\Model\ResourceModel\ScheduledAction as ScheduledActionResource;
use M2E\TikTokShop\Model\ResourceModel\StopQueue as StopQueueResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class ProductHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\TikTokShop\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installProductTable($setup);
        $this->installProductVariantSkuTable($setup);
        $this->installGlobalProductTable($setup);
        $this->installGlobalProductVariantSkuTable($setup);
        $this->installProductInstructionTable($setup);
        $this->installProductScheduledActionTable($setup);
        $this->installImageTable($setup);
        $this->installProductImageRelationTable($setup);
        $this->installStopQueueTable($setup);
        $this->installUnmanagedProductTable($setup);
        $this->installUnmanagedVariantTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installProductTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT);

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
                ListingProductResource::COLUMN_GLOBAL_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true]
            )
            ->addColumn(
                ListingProductResource::COLUMN_STATUS_CHANGER,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingProductResource::COLUMN_IS_GIFT,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => \M2E\TikTokShop\Model\Product::IS_GIFT_OFF]
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
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_IS_VALID_CATEGORY_ATTRIBUTES,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => null],
            )
            ->addColumn(
                ListingProductResource::COLUMN_CATEGORY_ATTRIBUTES_ERRORS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_MANUFACTURER_ID,
                Table::TYPE_TEXT,
                255,
                ['default' => null],
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_RESPONSIBLE_PERSON_IDS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null],
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
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_LISTING_QUALITY_TIER,
                Table::TYPE_TEXT,
                20,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_LISTING_QUALITY_RECOMMENDATIONS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_AUDIT_FAILED_REASONS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_MANUFACTURER_CONFIG_ID,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true]
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
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_VARIANT_SKU);

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
                ListingProductVariantResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_ONLINE_WAREHOUSE_ID,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
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
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_INSTRUCTION);

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
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_SCHEDULED_ACTION);

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
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                ScheduledActionResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
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
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_IMAGE);

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
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_IMAGE_RELATION);

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
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_STOP_QUEUE);

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

    private function installUnmanagedProductTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_UNMANAGED_PRODUCT);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                UnmanagedProductResource::COLUMN_ID,
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
                UnmanagedProductResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_SHOP_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_TTS_PRODUCT_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_IS_SIMPLE,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_MIN_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_MAX_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_IS_GIFT,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => \M2E\TikTokShop\Model\Product::IS_GIFT_OFF]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_CATEGORY_ID,
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_CATEGORIES_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                UnmanagedProductResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('account_id', UnmanagedProductResource::COLUMN_ACCOUNT_ID)
            ->addIndex('shop_id', UnmanagedProductResource::COLUMN_SHOP_ID)
            ->addIndex('tts_product_id', UnmanagedProductResource::COLUMN_TTS_PRODUCT_ID)
            ->addIndex('magento_product_id', UnmanagedProductResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addIndex('qty', UnmanagedProductVariantResource::COLUMN_QTY)
            ->addIndex('status', UnmanagedProductResource::COLUMN_STATUS)
            ->addIndex('title', UnmanagedProductResource::COLUMN_TITLE)
            ->addIndex('category_id', UnmanagedProductResource::COLUMN_CATEGORY_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installUnmanagedVariantTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_UNMANAGED_PRODUCT_VARIANT_SKU);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_ID,
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
                UnmanagedProductVariantResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_SHOP_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_SKU_ID,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_SKU,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_WAREHOUSE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_CURRENCY,
                Table::TYPE_TEXT,
                10,
                ['nullable' => false]
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_SALES_ATTRIBUTES,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_IDENTIFIER_ID,
                Table::TYPE_TEXT,
                50,
                ['default' => null]
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_IDENTIFIER_TYPE,
                Table::TYPE_TEXT,
                10,
                ['default' => null]
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                UnmanagedProductVariantResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('product_id', UnmanagedProductVariantResource::COLUMN_PRODUCT_ID)
            ->addIndex('account_id', UnmanagedProductVariantResource::COLUMN_ACCOUNT_ID)
            ->addIndex('shop_id', UnmanagedProductVariantResource::COLUMN_SHOP_ID)
            ->addIndex('magento_product_id', UnmanagedProductVariantResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addIndex('warehouse_id', UnmanagedProductVariantResource::COLUMN_WAREHOUSE_ID)
            ->addIndex('status', UnmanagedProductVariantResource::COLUMN_STATUS)
            ->addIndex('sku_id', UnmanagedProductVariantResource::COLUMN_SKU_ID)
            ->addIndex('sku', UnmanagedProductVariantResource::COLUMN_SKU)
            ->addIndex('currency', UnmanagedProductVariantResource::COLUMN_CURRENCY)
            ->addIndex('qty', UnmanagedProductVariantResource::COLUMN_QTY)
            ->addIndex('price', UnmanagedProductVariantResource::COLUMN_PRICE)
            ->addIndex('identifier_id', UnmanagedProductVariantResource::COLUMN_IDENTIFIER_ID)
            ->addIndex('identifier_type', UnmanagedProductVariantResource::COLUMN_IDENTIFIER_TYPE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installGlobalProductTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_GLOBAL_PRODUCT));

        $table
            ->addColumn(
                GlobalProductResource::COLUMN_ID,
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
                GlobalProductResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_SOURCE_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_GLOBAL_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true,]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_DESCRIPTION,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => false]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_CATEGORY_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_BRAND_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_PACKAGE_DIMENSIONS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => false]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_PACKAGE_WEIGHT,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => false]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_MAIN_IMAGES,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => false]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_CERTIFICATIONS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_PRODUCT_ATTRIBUTES,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_SIZE_CHART,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_MANUFACTURER_IDS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_RESPONSIBLE_PERSON_IDS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_SOURCE_LOCALE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                GlobalProductResource::COLUMN_UPDATE_DATE,
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

    private function installGlobalProductVariantSkuTable(\Magento\Framework\Setup\SetupInterface $setup)
    {
        $table = $setup
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_GLOBAL_PRODUCT_VARIANT_SKU));

        $table
            ->addColumn(
                GlobalProductVariantSkuResource::COLUMN_ID,
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
                GlobalProductVariantSkuResource::COLUMN_GLOBAL_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true]
            )
            ->addColumn(
                GlobalProductVariantSkuResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true]
            )
            ->addColumn(
                GlobalProductVariantSkuResource::COLUMN_GLOBAL_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true,]
            )
            ->addColumn(
                GlobalProductVariantSkuResource::COLUMN_SALES_ATTRIBUTES,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true]
            )
            ->addColumn(
                GlobalProductVariantSkuResource::COLUMN_SELLER_SKU,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                GlobalProductVariantSkuResource::COLUMN_PRICE,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true]
            )
            ->addColumn(
                GlobalProductVariantSkuResource::COLUMN_IDENTIFIER_CODE,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['nullable' => true]
            )
            ->addColumn(
                GlobalProductVariantSkuResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                GlobalProductVariantSkuResource::COLUMN_UPDATE_DATE,
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
