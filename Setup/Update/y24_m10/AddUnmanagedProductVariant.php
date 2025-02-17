<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m10;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct as UnmanagedProductResource;
use M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku as UnmanagedProductVariantResource;
use Magento\Framework\DB\Ddl\Table;

class AddUnmanagedProductVariant extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createNewUnmanagedProductTable();
        $this->createNewUnmanagedVariantTable();

        $this->deleteOldTable();
        $this->resetInventoryLastSyncDate();
    }

    private function createNewUnmanagedProductTable(): void
    {
        $table = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_UNMANAGED_PRODUCT));

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
                UnmanagedProductResource::COLUMN_IS_SIMPLE,
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

        $this->getConnection()->createTable($table);
    }

    private function createNewUnmanagedVariantTable(): void
    {
        $table = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_UNMANAGED_PRODUCT_VARIANT_SKU));

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

        $this->getConnection()->createTable($table);
    }

    private function deleteOldTable(): void
    {
        $oldTableName = $this->getFullTableName(TablesHelper::PREFIX . 'listing_other');
        $this->getConnection()->dropTable($oldTableName);
    }

    private function resetInventoryLastSyncDate(): void
    {
        $this->getConnection()->update(
            $this->getFullTableName(TablesHelper::TABLE_NAME_SHOP),
            [\M2E\TikTokShop\Model\ResourceModel\Shop::COLUMN_INVENTORY_LAST_SYNC => new \Zend_Db_Expr('NULL')],
        );
    }
}
