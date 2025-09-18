<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\InstallHandler;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Order as OrderResource;
use M2E\TikTokShop\Model\ResourceModel\Order\Change as OrderChangeResource;
use M2E\TikTokShop\Model\ResourceModel\Order\Item as OrderItemResource;
use M2E\TikTokShop\Model\ResourceModel\Order\Note as OrderNoteResource;
use Magento\Framework\DB\Ddl\Table;

class OrderHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\TikTokShop\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installOrderTable($setup);
        $this->installOrderItemTable($setup);
        $this->installOrderNoteTable($setup);
        $this->installOrderChangeTable($setup);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
    }

    private function installOrderTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_ORDER);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                OrderResource::COLUMN_ID,
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
                OrderResource::COLUMN_ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_SHOP_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_STORE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILURE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderResource::COLUMN_RESERVATION_STATE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_RESERVATION_START_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT
            )
            ->addColumn(
                OrderResource::COLUMN_TTS_ORDER_ID,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderResource::COLUMN_ORDER_STATUS,
                Table::TYPE_TEXT,
                30
            )
            ->addColumn(
                OrderResource::COLUMN_WAREHOUSE_ID,
                Table::TYPE_TEXT,
                30
            )
            ->addColumn(
                OrderResource::COLUMN_PURCHASE_CREATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderResource::COLUMN_PURCHASE_UPDATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderResource::COLUMN_PAID_AMOUNT,
                Table::TYPE_DECIMAL,
                [12, 4],
                [
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_CURRENCY,
                Table::TYPE_TEXT,
                10,
                [
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderResource::COLUMN_TAX_DETAILS,
                Table::TYPE_TEXT
            )
            ->addColumn(
                OrderResource::COLUMN_BUYER_USER_ID,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderResource::COLUMN_BUYER_NAME,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderResource::COLUMN_BUYER_EMAIL,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderResource::COLUMN_BUYER_MESSAGE,
                Table::TYPE_TEXT
            )
            ->addColumn(
                OrderResource::COLUMN_PAYMENT_METHOD_NAME,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderResource::COLUMN_PAYMENT_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderResource::COLUMN_PAYMENT_DETAILS,
                Table::TYPE_TEXT
            )
            ->addColumn(
                OrderResource::COLUMN_SHIPPING_DETAILS,
                Table::TYPE_TEXT
            )
            ->addColumn(
                OrderResource::COLUMN_DELIVER_BY_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderResource::COLUMN_BUYER_CANCELLATION_REQUEST,
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0]
            )
            ->addColumn(
                OrderResource::COLUMN_BUYER_CANCELLATION_REQUEST_REASON,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderResource::COLUMN_SHIP_BY_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderResource::COLUMN_IS_SAMPLE,
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0]
            )
            ->addColumn(
                OrderResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addIndex('tts_order_id', OrderResource::COLUMN_TTS_ORDER_ID)
            ->addIndex('buyer_email', OrderResource::COLUMN_BUYER_EMAIL)
            ->addIndex('buyer_name', OrderResource::COLUMN_BUYER_NAME)
            ->addIndex('buyer_user_id', OrderResource::COLUMN_BUYER_USER_ID)
            ->addIndex('paid_amount', OrderResource::COLUMN_PAID_AMOUNT)
            ->addIndex('purchase_create_date', OrderResource::COLUMN_PURCHASE_CREATE_DATE)
            ->addIndex('ship_by_date', OrderResource::COLUMN_SHIP_BY_DATE)
            ->addIndex('deliver_by_date', OrderResource::COLUMN_DELIVER_BY_DATE)
            ->addIndex('account_id', OrderResource::COLUMN_ACCOUNT_ID)
            ->addIndex('magento_order_id', OrderResource::COLUMN_MAGENTO_ORDER_ID)
            ->addIndex(
                'magento_order_creation_failure',
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILURE
            )
            ->addIndex(
                'magento_order_creation_fails_count',
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_FAILS_COUNT
            )
            ->addIndex(
                'magento_order_creation_latest_attempt_date',
                OrderResource::COLUMN_MAGENTO_ORDER_CREATION_LATEST_ATTEMPT_DATE
            )
            ->addIndex('shop_id', OrderResource::COLUMN_SHOP_ID)
            ->addIndex('reservation_state', OrderResource::COLUMN_RESERVATION_STATE)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installOrderItemTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_ITEM);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                OrderItemResource::COLUMN_ID,
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
                OrderItemResource::COLUMN_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_PRODUCT_DETAILS,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_QTY_RESERVED,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => 0]
            )
            ->addColumn(
                OrderItemResource::COLUMN_ADDITIONAL_DATA,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_TTS_ITEM_ID,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderItemResource::COLUMN_ITEM_STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['default' => \M2E\TikTokShop\Model\Order\Item::ITEM_STATUS_UNKNOWN, 'unsigned' => true]
            )
            ->addColumn(
                OrderItemResource::COLUMN_TTS_PRODUCT_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_TTS_SKU_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_PACKAGE_ID,
                Table::TYPE_TEXT,
                30
            )
            ->addColumn(
                OrderItemResource::COLUMN_TITLE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_SKU,
                Table::TYPE_TEXT,
                64,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_QTY_PURCHASED,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                OrderItemResource::COLUMN_SALE_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                OrderItemResource::COLUMN_ORIGINAL_PRICE,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                OrderItemResource::COLUMN_PLATFORM_DISCOUNT,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                OrderItemResource::COLUMN_SELLER_DISCOUNT,
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000']
            )
            ->addColumn(
                OrderItemResource::COLUMN_TAX_DETAILS,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_TRACKING_DETAILS,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_BUYER_REQUEST_REFUND,
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0]
            )
            ->addColumn(
                OrderItemResource::COLUMN_BUYER_REQUEST_RETURN,
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0]
            )
            ->addColumn(
                OrderItemResource::COLUMN_REFUND_RETURN_ID,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_REFUND_RETURN_STATUS,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_CANCEL_REASON,
                Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                OrderItemResource::COLUMN_SHIPPING_IN_PROGRESS,
                Table::TYPE_SMALLINT,
                null,
                ['default' => \M2E\TikTokShop\Model\Order\Item::SHIPPING_IS_IN_PROGRESS_NO, 'unsigned' => true]
            )
            ->addColumn(
                OrderItemResource::COLUMN_IS_GIFT,
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0]
            )
            ->addColumn(
                OrderItemResource::COLUMN_COMBINED_LISTING_SKUS,
                Table::TYPE_TEXT,
                \M2E\Core\Model\ResourceModel\Setup::LONG_COLUMN_SIZE,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                OrderItemResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('tts_item_id', OrderItemResource::COLUMN_TTS_ITEM_ID)
            ->addIndex('tts_product_id', OrderItemResource::COLUMN_TTS_PRODUCT_ID)
            ->addIndex('tts_sku_id', OrderItemResource::COLUMN_TTS_SKU_ID)
            ->addIndex('sku', OrderItemResource::COLUMN_SKU)
            ->addIndex('title', OrderItemResource::COLUMN_TITLE)
            ->addIndex('order_id', OrderItemResource::COLUMN_ORDER_ID)
            ->addIndex('product_id', OrderItemResource::COLUMN_PRODUCT_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installOrderNoteTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_NOTE);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                OrderNoteResource::COLUMN_ID,
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
                OrderNoteResource::COLUMN_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderNoteResource::COLUMN_NOTE,
                Table::TYPE_TEXT
            )
            ->addColumn(
                OrderNoteResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderNoteResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addIndex('order_id', OrderNoteResource::COLUMN_ORDER_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }

    private function installOrderChangeTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_ORDER_CHANGE);

        $table = $setup->getConnection()->newTable($tableName);

        $table
            ->addColumn(
                OrderChangeResource::COLUMN_ID,
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
                OrderChangeResource::COLUMN_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_ACTION,
                Table::TYPE_TEXT,
                50,
                ['nullable' => false]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_PARAMS,
                Table::TYPE_TEXT
            )
            ->addColumn(
                OrderChangeResource::COLUMN_CREATOR_TYPE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => 0,
                ]
            )
            ->addColumn(
                OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_DATE,
                Table::TYPE_DATETIME,
            )
            ->addColumn(
                OrderChangeResource::COLUMN_HASH,
                Table::TYPE_TEXT,
                50
            )
            ->addColumn(
                OrderChangeResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addColumn(
                OrderChangeResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME
            )
            ->addIndex('action', OrderChangeResource::COLUMN_ACTION)
            ->addIndex('creator_type', OrderChangeResource::COLUMN_CREATOR_TYPE)
            ->addIndex('hash', OrderChangeResource::COLUMN_HASH)
            ->addIndex('order_id', OrderChangeResource::COLUMN_ORDER_ID)
            ->addIndex('processing_attempt_count', OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($table);
    }
}
