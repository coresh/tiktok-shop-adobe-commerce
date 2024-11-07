<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Listing;

class Other extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SHOP_ID = 'shop_id';
    public const COLUMN_WAREHOUSE_ID = 'warehouse_id';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_MOVED_TO_LISTING_PRODUCT_ID = 'moved_to_listing_product_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_TTS_PRODUCT_ID = 'tts_product_id';
    public const COLUMN_SKU_ID = 'sku_id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_SKU = 'sku';
    public const COLUMN_CURRENCY = 'currency';
    public const COLUMN_PRICE = 'price';
    public const COLUMN_QTY = 'qty';
    public const COLUMN_INVENTORY_DATA = 'inventory_data';
    public const COLUMN_CATEGORY_ID = 'category_id';
    public const COLUMN_CATEGORIES_DATA = 'categories_data';
    public const COLUMN_IDENTIFIER_ID = 'identifier_id';
    public const COLUMN_IDENTIFIER_TYPE = 'identifier_type';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_LISTING_OTHER,
            self::COLUMN_ID
        );
    }
}
