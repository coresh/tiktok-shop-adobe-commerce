<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Promotion;

class Product extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_PROMOTION_ID = 'promotion_id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SHOP_ID = 'shop_id';
    public const COLUMN_PRODUCT_ID = 'product_id';
    public const COLUMN_PRODUCT_FIXED_PRICE = 'product_fixed_price';
    public const COLUMN_PRODUCT_DISCOUNT = 'product_discount';
    public const COLUMN_PRODUCT_QUANTITY_LIMIT = 'product_quantity_limit';
    public const COLUMN_PRODUCT_PER_USER = 'product_per_user';
    public const COLUMN_SKU_ID = 'sku_id';
    public const COLUMN_SKU_FIXED_PRICE = 'sku_fixed_price';
    public const COLUMN_SKU_DISCOUNT = 'sku_discount';
    public const COLUMN_SKU_QUANTITY_LIMIT = 'sku_quantity_limit';
    public const COLUMN_SKU_PER_USER = 'sku_per_user';

    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PROMOTION_PRODUCT,
            self::COLUMN_ID
        );
    }
}
