<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct;

class VariantSku extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_PRODUCT_ID = 'product_id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_SHOP_ID = 'shop_id';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_SKU_ID = 'sku_id';
    public const COLUMN_WAREHOUSE_ID = 'warehouse_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_SKU = 'sku';
    public const COLUMN_PRICE = 'price';
    public const COLUMN_CURRENCY = 'currency';
    public const COLUMN_QTY = 'qty';
    public const COLUMN_SALES_ATTRIBUTES = 'sales_attributes';
    public const COLUMN_IDENTIFIER_TYPE = 'identifier_type';
    public const COLUMN_IDENTIFIER_ID = 'identifier_id';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_UNMANAGED_PRODUCT_VARIANT_SKU,
            self::COLUMN_ID
        );
    }
}
