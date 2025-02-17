<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m02;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Product as ListingProductResource;
use M2E\TikTokShop\Model\ResourceModel\Product\VariantSku as ListingProductVariantResource;
use Magento\Framework\DB\Ddl\Table;

class AddProductVariantSku extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $oldProductTableName = $this->createBackupForProductTable();

        $this->createNewProductTable();
        $this->createVariantSkuTable();

        $this->migrateDataFromOldTable($oldProductTableName);

        $this->modifyUnmanagedProductTable();
    }

    private function createBackupForProductTable(): string
    {
        $backupTableName = $this->getFullTableName(TablesHelper::PREFIX . 'backup_product');
        $this->renameTable(TablesHelper::TABLE_NAME_PRODUCT, $backupTableName);

        return $backupTableName;
    }

    private function createNewProductTable(): void
    {
        $listingProductTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT));

        $listingProductTable
            ->addColumn(
                ListingProductResource::COLUMN_ID,
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
                ListingProductResource::COLUMN_LISTING_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ListingProductResource::COLUMN_TTS_PRODUCT_ID,
                Table::TYPE_TEXT,
                50,
            )
            ->addColumn(
                ListingProductResource::COLUMN_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                ListingProductResource::COLUMN_STATUS_CHANGER,
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_TITLE,
                Table::TYPE_TEXT,
                255,
                ['default' => null],
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_DESCRIPTION,
                Table::TYPE_TEXT,
                40,
                ['default' => null],
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_BRAND_ID,
                Table::TYPE_TEXT,
                30,
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_BRAND_NAME,
                Table::TYPE_TEXT,
                255,
            )
            ->addColumn(
                'online_price',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_CATEGORY,
                Table::TYPE_TEXT,
                255,
                ['default' => null],
            )
            ->addColumn(
                ListingProductResource::COLUMN_ONLINE_CATEGORIES_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null],
            )
            ->addColumn(
                ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null],
            )
            ->addColumn(
                'template_description_mode',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                'template_description_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null],
            )
            ->addColumn(
                'template_selling_format_mode',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                'template_selling_format_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null],
            )
            ->addColumn(
                'template_synchronization_mode',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
            )
            ->addColumn(
                'template_synchronization_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null],
            )
            ->addColumn(
                ListingProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                ListingProductResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null],
            )
            ->addColumn(
                ListingProductResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                ListingProductResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addIndex('listing_id', ListingProductResource::COLUMN_LISTING_ID)
            ->addIndex('magento_product_id', ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addIndex('status', ListingProductResource::COLUMN_STATUS)
            ->addIndex('status_changer', ListingProductResource::COLUMN_STATUS_CHANGER)
            ->addIndex('online_category', ListingProductResource::COLUMN_ONLINE_CATEGORY)
            ->addIndex('online_title', ListingProductResource::COLUMN_ONLINE_TITLE)
            ->addIndex('template_category_id', ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID)
            ->addIndex('template_description_mode', 'template_description_mode')
            ->addIndex('template_description_id', 'template_description_id')
            ->addIndex('template_selling_format_mode', 'template_selling_format_mode')
            ->addIndex('template_selling_format_id', 'template_selling_format_id')
            ->addIndex('template_synchronization_mode', 'template_synchronization_mode')
            ->addIndex('template_synchronization_id', 'template_synchronization_id')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($listingProductTable);
    }

    private function createVariantSkuTable(): void
    {
        $listingProductVariantTable = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_VARIANT_SKU));

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
                ],
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_MAGENTO_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_SKU_ID,
                Table::TYPE_TEXT,
                50,
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_WAREHOUSE_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_ONLINE_SKU,
                Table::TYPE_TEXT,
                255,
                ['default' => null],
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_ONLINE_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null],
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_ONLINE_QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null],
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addColumn(
                ListingProductVariantResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null],
            )
            ->addIndex('product_id', ListingProductVariantResource::COLUMN_PRODUCT_ID)
            ->addIndex('magento_product_id', ListingProductVariantResource::COLUMN_MAGENTO_PRODUCT_ID)
            ->addIndex('sku_id', ListingProductVariantResource::COLUMN_SKU_ID)
            ->addIndex('warehouse_id', ListingProductVariantResource::COLUMN_WAREHOUSE_ID)
            ->addIndex('warehouse_id', ListingProductVariantResource::COLUMN_WAREHOUSE_ID)
            ->addIndex('online_qty', ListingProductVariantResource::COLUMN_ONLINE_QTY)
            ->addIndex('online_price', ListingProductVariantResource::COLUMN_ONLINE_PRICE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($listingProductVariantTable);
    }

    private function migrateDataFromOldTable(string $oldProductTableName): void
    {
        $stmt = $this->getConnection()
                     ->select()
                     ->from($oldProductTableName)
                     ->query();
        while ($row = $stmt->fetch()) {
            $productId = (int)$row['id'];

            $this->getConnection()->insert(
                $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT),
                [
                    ListingProductResource::COLUMN_ID => $productId,
                    ListingProductResource::COLUMN_LISTING_ID => $row['listing_id'],
                    ListingProductResource::COLUMN_MAGENTO_PRODUCT_ID => ($magentoProductId = $row['magento_product_id']),
                    ListingProductResource::COLUMN_TTS_PRODUCT_ID => $row['product_id'],
                    ListingProductResource::COLUMN_STATUS => $row['status'],
                    ListingProductResource::COLUMN_STATUS_CHANGER => $row['status_changer'],
                    ListingProductResource::COLUMN_ONLINE_TITLE => $row['online_title'],
                    ListingProductResource::COLUMN_ONLINE_DESCRIPTION => $row['online_description'],
                    ListingProductResource::COLUMN_ONLINE_BRAND_ID => $row['online_brand_id'],
                    ListingProductResource::COLUMN_ONLINE_BRAND_NAME => $row['online_brand_name'],
                    'online_price' => $row['online_price'],
                    ListingProductResource::COLUMN_ONLINE_QTY => $row['online_qty'],
                    ListingProductResource::COLUMN_ONLINE_CATEGORY => $row['online_main_category'],
                    ListingProductResource::COLUMN_ONLINE_CATEGORIES_DATA => $row['online_categories_data'],
                    ListingProductResource::COLUMN_TEMPLATE_CATEGORY_ID => $row['template_category_id'],
                    'template_description_mode' => $row['template_description_mode'],
                    'template_description_id' => $row['template_description_id'],
                    'template_selling_format_mode' => $row['template_selling_format_mode'],
                    'template_selling_format_id' => $row['template_selling_format_id'],
                    'template_synchronization_mode' => $row['template_selling_format_mode'],
                    'template_synchronization_id' => $row['template_selling_format_id'],
                    ListingProductResource::COLUMN_LAST_BLOCKING_ERROR_DATE => $row['last_blocking_error_date'],
                    ListingProductResource::COLUMN_ADDITIONAL_DATA => $row['additional_data'],
                    ListingProductResource::COLUMN_UPDATE_DATE => ($updateDate = $row['update_date']),
                    ListingProductResource::COLUMN_CREATE_DATE => ($createDate = $row['create_date']),
                ],
            );

            $this->getConnection()->insert(
                $this->getFullTableName(TablesHelper::TABLE_NAME_PRODUCT_VARIANT_SKU),
                [
                    ListingProductVariantResource::COLUMN_PRODUCT_ID => $productId,
                    ListingProductVariantResource::COLUMN_MAGENTO_PRODUCT_ID => $magentoProductId,
                    ListingProductVariantResource::COLUMN_SKU_ID => $row['sku_id'],
                    ListingProductVariantResource::COLUMN_WAREHOUSE_ID => $row['warehouse_id'],
                    ListingProductVariantResource::COLUMN_ONLINE_SKU => $row['online_sku'],
                    ListingProductVariantResource::COLUMN_ONLINE_PRICE => $row['online_price'],
                    ListingProductVariantResource::COLUMN_ONLINE_QTY => $row['online_qty'],
                    ListingProductVariantResource::COLUMN_UPDATE_DATE => $updateDate,
                    ListingProductVariantResource::COLUMN_CREATE_DATE => $createDate,
                ],
            );
        }
    }

    private function modifyUnmanagedProductTable(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::PREFIX . 'listing_other');
        $modifier
            ->addColumn(
                'warehouse_id',
                'INT UNSIGNED DEFAULT NULL',
                null,
                'shop_id',
                true,
                false,
            )
            ->addColumn(
                'category_id',
                'VARCHAR(255)',
                null,
                'inventory_data',
                false,
                false,
            )
            ->commit();

        $modifier->renameColumn('product_id', 'tts_product_id');
        $modifier->dropIndex('account_id__shop_id__product_id__sku_id');
    }
}
