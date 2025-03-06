<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m02;

use M2E\TikTokShop\Helper\Module\Database\Tables;
use M2E\TikTokShop\Model\ResourceModel\GlobalProduct as GlobalProductResource;
use M2E\TikTokShop\Model\ResourceModel\GlobalProduct\VariantSku as GlobalProductVariantSkuResource;
use Magento\Framework\DB\Ddl\Table;

class AddGlobalProduct extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createGlobalProductTable();
        $this->createGlobalProductVariantSkuTable();
        $this->addGlobalProductIdToProductTable();
    }

    private function createGlobalProductTable()
    {
        $table = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_GLOBAL_PRODUCT));

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

        $this->getConnection()->createTable($table);
    }

    private function createGlobalProductVariantSkuTable()
    {
        $table = $this
            ->getConnection()
            ->newTable($this->getFullTableName(Tables::TABLE_NAME_GLOBAL_PRODUCT_VARIANT_SKU));

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

        $this->getConnection()->createTable($table);
    }

    private function addGlobalProductIdToProductTable()
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT);

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Product::COLUMN_GLOBAL_PRODUCT_ID,
            'INTEGER UNSIGNED',
            null,
            null,
            false,
            false
        );

        $modifier->commit();
    }
}
