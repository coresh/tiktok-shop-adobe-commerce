<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m10;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Promotion as PromotionResource;
use M2E\TikTokShop\Model\ResourceModel\Promotion\Product as ProductPromotion;
use Magento\Framework\DB\Ddl\Table;

class AddPromotion extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createPromotionTable();
        $this->createPromotionProductTable();
    }

    private function createPromotionTable()
    {
        $table = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_PROMOTION));

        $table
            ->addColumn(
                PromotionResource::COLUMN_ID,
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
                PromotionResource::COLUMN_TYPE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                PromotionResource::COLUMN_PROMOTION_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                PromotionResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                PromotionResource::COLUMN_SHOP_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                PromotionResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                PromotionResource::COLUMN_STATUS,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                PromotionResource::COLUMN_PRODUCT_LEVEL,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                PromotionResource::COLUMN_START_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                PromotionResource::COLUMN_END_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                PromotionResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                PromotionResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');
        $this->getConnection()->createTable($table);
    }

    private function createPromotionProductTable(): void
    {
        $table = $this
            ->getConnection()
            ->newTable($this->getFullTableName(TablesHelper::TABLE_NAME_PROMOTION_PRODUCT));

        $table
            ->addColumn(
                ProductPromotion::COLUMN_ID,
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
                ProductPromotion::COLUMN_PROMOTION_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductPromotion::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductPromotion::COLUMN_SHOP_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                ProductPromotion::COLUMN_PRODUCT_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                ProductPromotion::COLUMN_PRODUCT_FIXED_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ProductPromotion::COLUMN_PRODUCT_DISCOUNT,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductPromotion::COLUMN_PRODUCT_QUANTITY_LIMIT,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false]
            )
            ->addColumn(
                ProductPromotion::COLUMN_PRODUCT_PER_USER,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false]
            )
            ->addColumn(
                ProductPromotion::COLUMN_SKU_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductPromotion::COLUMN_SKU_FIXED_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                ProductPromotion::COLUMN_SKU_DISCOUNT,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                ProductPromotion::COLUMN_SKU_QUANTITY_LIMIT,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false]
            )
            ->addColumn(
                ProductPromotion::COLUMN_SKU_PER_USER,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false]
            )
            ->addColumn(
                ProductPromotion::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                ProductPromotion::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $this->getConnection()->createTable($table);
    }
}
